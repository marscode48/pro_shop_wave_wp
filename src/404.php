<?php

/**
 * 404 Page
 *
 * @package proshopwave
 */

get_header();

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
?>

<main id="primary" class="not-found section">
  <div class="not-found__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <div class="not-found__content fadeup">
      <p class="not-found__eyebrow">
        <?php echo esc_html__('404 NOT FOUND', 'proshopwave'); ?>
      </p>

      <h1 class="not-found__title section__title">
        <?php echo esc_html__('ページが見つかりませんでした。', 'proshopwave'); ?>
      </h1>

      <p class="not-found__lead">
        <?php echo esc_html__('URLが変更されたか、ページが削除された可能性があります。商品一覧やトップページから目的のページをお探しください。', 'proshopwave'); ?>
      </p>

      <div class="not-found__actions">
        <a class="not-found__button section__button" href="<?php echo esc_url($shop_url); ?>">
          <span class="section__button-inner"><?php echo esc_html__('商品一覧を見る', 'proshopwave'); ?></span>
        </a>

        <a class="not-found__button section__button section__button--secondary" href="<?php echo esc_url(home_url('/blog/')); ?>">
          <span class="section__button-inner"><?php echo esc_html__('ブログを見る', 'proshopwave'); ?></span>
        </a>

        <a class="not-found__text-link" href="<?php echo esc_url(home_url('/')); ?>">
          <?php echo esc_html__('トップページへ戻る', 'proshopwave'); ?>
        </a>
      </div>
    </div>

  </div>
</main>

<?php
get_footer();
