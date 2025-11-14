<?php

/**
 * Template: Unified Search (Products / Posts Tabs)
 * Location: theme-root/src/search.php
 *
 * 概要:
 *  - 検索語に対して「商品」と「記事」をタブで切り替えて表示する検索テンプレート。
 *  - WooCommerce 有効時は product を、通常記事は post を対象に個別クエリ。
 *  - アクセシビリティ対応（WAI-ARIA ロール, aria-controls/selected, keyboard）。
 *  - BEM設計: .search, .search-tabs, .search-results などのブロック/エレメントを採用。
 *
 * 注意:
 *  - 2つのタブでページングを独立管理するのは複雑なため、本テンプレートでは
 *    初期実装として「各タブ上位 N 件 + もっと見る(専用検索)」リンクで対応します。
 */

if (! defined('ABSPATH')) {
  exit;
}

get_header();

$q = get_search_query();
$on_shop_ctx = function_exists('is_woocommerce') && is_woocommerce();

// 初期表示タブを決める: 明示的に ?tab=posts / ?tab=products を優先
$requested_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
if ($requested_tab !== 'products' && $requested_tab !== 'posts') {
  $requested_tab = '';
}

// 各タブの表示件数
$products_per_page = 12;
$posts_per_page    = 10;

// 商品クエリ（WooCommerceがある場合のみ）
$products_query = null;
$products_count = 0;
if (class_exists('WooCommerce')) {
  $products_query = new WP_Query([
    'post_type'      => 'product',
    's'              => $q,
    'posts_per_page' => $products_per_page,
    'post_status'    => 'publish',
    'no_found_rows'  => false,
    'ignore_sticky_posts' => true,
  ]);
  $products_count = intval($products_query->found_posts);
}

// 記事クエリ
$posts_query = new WP_Query([
  'post_type'      => 'post',
  's'              => $q,
  'posts_per_page' => $posts_per_page,
  'post_status'    => 'publish',
  'no_found_rows'  => false,
  'ignore_sticky_posts' => true,
]);
$posts_count = intval($posts_query->found_posts);

// デフォルトのタブ決定ロジック
$default_tab = 'products';
if ($requested_tab) {
  $default_tab = $requested_tab;
} else {
  if ($products_count === 0 && $posts_count > 0) {
    $default_tab = 'posts';
  }
}

// タブID
$products_panel_id = 'search-panel-products';
$posts_panel_id    = 'search-panel-posts';
?>

<main id="primary" class="search section">
  <div class="search__inner l-container l-container--wide">
    <?php get_template_part('template-parts/breadcrumb/breadcrumb'); ?>
    <div class="search__header fadeup">
      <h1 class="search__title  section__title">
        <?php echo esc_html__('検索結果', 'proshopwave'); ?>
      </h1>
      <?php if ($q) : ?>
        <p class="search__keyword">
          <?php echo esc_html__('キーワード: ', 'proshopwave'); ?>
          <mark class="search__keyword-mark"><?php echo esc_html($q); ?></mark>
        </p>
      <?php endif; ?>
    </div>

    <div class="search__wrapper fadeup">
      <!-- タブヘッダー -->
      <div class="search-tabs" role="tablist" aria-label="<?php echo esc_attr__('検索結果タブ', 'proshopwave'); ?>">
        <?php if (class_exists('WooCommerce')) : ?>
          <?php $is_products_active = ($default_tab === 'products'); ?>
          <button
            class="search-tabs__tab<?php echo $is_products_active ? ' is-active' : ''; ?>"
            id="tab-products"
            role="tab"
            aria-selected="<?php echo $is_products_active ? 'true' : 'false'; ?>"
            aria-controls="<?php echo esc_attr($products_panel_id); ?>"
            data-tab-target="<?php echo esc_attr($products_panel_id); ?>"
            type="button">
            <?php echo esc_html__('商品', 'proshopwave'); ?>
            <span class="search-tabs__count"><?php echo esc_html($products_count); ?></span>
          </button>
        <?php endif; ?>

        <?php $is_posts_active = ($default_tab === 'posts' || (! class_exists('WooCommerce'))); ?>
        <button
          class="search-tabs__tab<?php echo $is_posts_active ? ' is-active' : ''; ?>"
          id="tab-posts"
          role="tab"
          aria-selected="<?php echo $is_posts_active ? 'true' : 'false'; ?>"
          aria-controls="<?php echo esc_attr($posts_panel_id); ?>"
          data-tab-target="<?php echo esc_attr($posts_panel_id); ?>"
          type="button">
          <?php echo esc_html__('記事', 'proshopwave'); ?>
          <span class="search-tabs__count"><?php echo esc_html($posts_count); ?></span>
        </button>
      </div>

      <!-- タブパネル: 商品 -->
      <?php if (class_exists('WooCommerce')) : ?>
        <section
          id="<?php echo esc_attr($products_panel_id); ?>"
          class="search-results search-results--products<?php echo ($default_tab === 'products' ? ' is-active' : ''); ?>"
          role="tabpanel"
          aria-labelledby="tab-products"
          tabindex="0">

          <?php if ($products_query && $products_query->have_posts()) : ?>
            <ul class="products search-grid search-grid--products">
              <?php while ($products_query->have_posts()) : $products_query->the_post(); ?>
                <?php wc_get_template_part('content', 'product'); ?>
              <?php endwhile;
              wp_reset_postdata(); ?>
            </ul>

            <?php if ($products_count > $products_per_page) : ?>
              <div class="search-results__more">
                <a class="section__button" href="<?php echo esc_url(add_query_arg(['s' => $q, 'post_type' => 'product'], home_url('/'))); ?>">
                  <span class="section__button-inner"><?php echo esc_html__('さらに商品を表示', 'proshopwave'); ?></span>
                </a>
              </div>
            <?php endif; ?>

          <?php else : ?>
            <p class="search-results__empty"><?php echo esc_html__('該当する商品は見つかりませんでした。', 'proshopwave'); ?></p>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <!-- タブパネル: 記事 -->
      <section
        id="<?php echo esc_attr($posts_panel_id); ?>"
        class="search-results search-results--posts<?php echo ($default_tab === 'posts' || (! class_exists('WooCommerce')) ? ' is-active' : ''); ?>"
        role="tabpanel"
        aria-labelledby="tab-posts"
        tabindex="0">

        <?php if ($posts_query->have_posts()) : ?>
          <div class="card-list card-list--blog">
            <?php while ($posts_query->have_posts()) : $posts_query->the_post(); ?>
              <?php get_template_part('template-parts/card/card-blog'); ?>
            <?php endwhile; ?>
          </div>
          <?php wp_reset_postdata(); ?>

          <?php if ($posts_count > $posts_per_page) : ?>
            <div class="search-results__more">
              <a class="section__button" href="<?php echo esc_url(add_query_arg(['s' => $q, 'post_type' => 'post'], home_url('/'))); ?>">
                <span class="section__button-inner"><?php echo esc_html__('さらに記事を表示', 'proshopwave'); ?></span>
              </a>
            </div>
          <?php endif; ?>

        <?php else : ?>
          <p class="search-results__empty"><?php echo esc_html__('該当する記事は見つかりませんでした。', 'proshopwave'); ?></p>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<?php get_footer();
