<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="product-archive section">
  <div class="product-archive__inner l-container l-container--wide">
    <?php
    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action('woocommerce_before_main_content');
    // デフォルトのWooCommerceパンくずリストを無効化し、テーマの統一パンくずリストを挿入
    get_template_part('template-parts/breadcrumb/breadcrumb');

    /**
     * Hook: woocommerce_shop_loop_header.
     *
     * @since 8.6.0
     *
     * @hooked woocommerce_product_taxonomy_archive_header - 10
     */
    do_action('woocommerce_shop_loop_header');

    if (woocommerce_product_loop()) {
    ?>
      <div class="product-archive__wrapper">
        <?php
        // 商品ループの上に通知だけ表示
        woocommerce_output_all_notices();

        // フィルターバーを挿入
        get_template_part('template-parts/filterbar/filterbar-woocommerce');
        ?>
        <div class="product-archive__controls fadeup">
          <?php
          // 商品数
          woocommerce_result_count();

          // 並び替えフォーム
          woocommerce_catalog_ordering();
          ?>
        </div>

        <div class="product-archive__list">
          <?php

          woocommerce_product_loop_start();

          if (wc_get_loop_prop('total')) {
            while (have_posts()) {
              the_post();

              /**
               * Hook: woocommerce_shop_loop.
               */
              do_action('woocommerce_shop_loop');

              wc_get_template_part('content', 'product');
            }
          }

          woocommerce_product_loop_end();

          ?>
        </div>
      </div>
    <?php

      /**
       * Hook: woocommerce_after_shop_loop.
       *
       * @hooked woocommerce_pagination - 10
       */
      do_action('woocommerce_after_shop_loop');
    } else {
      $shop_page_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
    ?>
      <div class="product-archive__empty-state fadeup">
        <p class="product-archive__empty-title">
          <?php echo esc_html__('該当する商品は見つかりませんでした。', 'proshopwave'); ?>
        </p>
        <p class="product-archive__empty-lead">
          <?php echo esc_html__('絞り込み条件を変更するか、商品一覧から他の商品もご覧ください。', 'proshopwave'); ?>
        </p>
        <a class="section__button" href="<?php echo esc_url($shop_page_url); ?>">
          <span class="section__button-inner"><?php echo esc_html__('商品一覧を見る', 'proshopwave'); ?></span>
        </a>
      </div>
    <?php
    }

    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action('woocommerce_after_main_content');
    ?>
  </div>
</div>

<?php
get_footer('shop');
