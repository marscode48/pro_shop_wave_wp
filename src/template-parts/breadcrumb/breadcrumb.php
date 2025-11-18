<?php

/**
 * Template part for Breadcrumb
 * WooCommerceページと通常ページの両方に対応
 * 
 * @package PRO_SHOP_WAVE
 */

// WooCommerceが有効かつWooCommerceページかどうかを判定
if (function_exists('is_woocommerce') && is_woocommerce()) {
  // WooCommerceのパンくずリストを出力
?>
  <nav class="breadcrumb faderight" aria-label="Breadcrumb">
    <?php woocommerce_breadcrumb(); ?>
  </nav>
<?php
} else {
  // 通常ページ用のパンくずリストを生成
?>
  <nav class="breadcrumb faderight" aria-label="Breadcrumb">
    <ul class="breadcrumb__list">
      <li class="breadcrumb__item">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__( 'ホーム', 'proshopwave' ); ?></a>
      </li>
      <?php
      if (is_category() || is_single()) {
        // 投稿・カテゴリー系: カテゴリー階層を <li> 単位で安全に出力
        if (is_category()) {
          $current_term = get_queried_object();
          if ($current_term && ! is_wp_error($current_term)) {
            $ancestor_ids = array_reverse(get_ancestors((int) $current_term->term_id, 'category'));
            // 祖先カテゴリへのリンク
            foreach ($ancestor_ids as $ancestor_id) {
              $link = get_category_link($ancestor_id);
              echo '<li class="breadcrumb__item"><a href="' . esc_url($link) . '">' . esc_html(get_cat_name($ancestor_id)) . '</a></li>';
            }
            // 現在のカテゴリ（リンク無し）
            echo '<li class="breadcrumb__item">' . esc_html(single_cat_title('', false)) . '</li>';
          }
        } else { // is_single()
          $categories = get_the_category();
          if (! empty($categories)) {
            // 先頭のカテゴリを主カテゴリとして扱う（必要に応じてYoast等のプライマリカテゴリに差し替え可）
            $primary = $categories[0];
            // 祖先カテゴリを上位→下位の順に並べる
            $ancestor_ids = array_reverse(get_ancestors((int) $primary->term_id, 'category'));
            foreach ($ancestor_ids as $ancestor_id) {
              $link = get_category_link($ancestor_id);
              echo '<li class="breadcrumb__item"><a href="' . esc_url($link) . '">' . esc_html(get_cat_name($ancestor_id)) . '</a></li>';
            }
            // 主カテゴリ自体（リンク付き）
            echo '<li class="breadcrumb__item"><a href="' . esc_url(get_category_link($primary->term_id)) . '">' . esc_html($primary->name) . '</a></li>';
          }
          // 投稿タイトル
          echo '<li class="breadcrumb__item">' . esc_html(get_the_title()) . '</li>';
        }
      } elseif (is_page()) {
        // 固定ページの場合、親ページの階層を表示
        global $post;
        if ($post->post_parent) {
          $parent_ids = array_reverse(get_post_ancestors($post->ID));
          foreach ($parent_ids as $parent_id) {
            echo '<li class="breadcrumb__item"><a href="' . get_permalink($parent_id) . '">' . get_the_title($parent_id) . '</a></li>';
          }
        }
        // 現在のページタイトルを表示
        echo '<li class="breadcrumb__item">' . get_the_title() . '</li>';
      } elseif (is_search()) {
        // 検索結果ページ
        echo '<li class="breadcrumb__item">' . esc_html__( '検索結果:', 'proshopwave' ) . ' ' . esc_html( get_search_query() ) . '</li>';
      } elseif (is_404()) {
        // 404ページ
        echo '<li class="breadcrumb__item">' . esc_html__( 'ページが見つかりません', 'proshopwave' ) . '</li>';
      } else {
        // その他のアーカイブ等
        echo '<li class="breadcrumb__item">' . esc_html( wp_title( '', false ) ) . '</li>';
      }
      ?>
    </ul>
  </nav>
<?php
}
?>