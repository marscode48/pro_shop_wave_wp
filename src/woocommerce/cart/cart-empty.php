<?php

/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$shop_page_id = wc_get_page_id('shop');
$shop_url     = $shop_page_id > 0 ? wc_get_page_permalink('shop') : home_url('/');
$shop_url     = apply_filters('woocommerce_return_to_shop_redirect', $shop_url);
$shop_text    = apply_filters('woocommerce_return_to_shop_text', __('Return to shop', 'woocommerce'));

$empty_cart_products = new WP_Query(
  array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => 3,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => 1,
  )
);
?>

<div class="woocommerce-empty-cart" role="region" aria-labelledby="woocommerce-empty-cart-title">
  <div class="woocommerce-empty-cart__content">
    <p class="woocommerce-empty-cart__eyebrow">YOUR CART IS EMPTY</p>

    <h2 class="woocommerce-empty-cart__title" id="woocommerce-empty-cart-title">
      <?php esc_html_e('現在カートには商品が入っていません。', 'proshopwave'); ?>
    </h2>

    <p class="woocommerce-empty-cart__text">
      <?php esc_html_e('気になるカスタムパーツやアパレル・グッズを探して、カスタムの準備を始めましょう。', 'proshopwave'); ?>
    </p>

    <div class="woocommerce-empty-cart__actions">
      <a class="button wc-backward woocommerce-empty-cart__button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" href="<?php echo esc_url($shop_url); ?>">
        <?php echo esc_html($shop_text); ?>
      </a>

      <a class="button woocommerce-empty-cart__button" href="<?php echo esc_url(home_url('/product-category/parts/')); ?>">
        <?php esc_html_e('カスタムパーツを見る', 'proshopwave'); ?>
      </a>

      <a class="button woocommerce-empty-cart__button" href="<?php echo esc_url(home_url('/product-category/apparel-goods/')); ?>">
        <?php esc_html_e('アパレル・グッズを見る', 'proshopwave'); ?>
      </a>
    </div>
  </div>

  <?php if ($empty_cart_products->have_posts()) : ?>
    <section class="woocommerce-empty-cart__products" aria-labelledby="woocommerce-empty-cart-products-title">
      <div class="woocommerce-empty-cart__products-header">
        <p class="woocommerce-empty-cart__products-eyebrow">NEW ARRIVALS</p>
        <h3 class="woocommerce-empty-cart__products-title" id="woocommerce-empty-cart-products-title">
          <?php esc_html_e('新着商品', 'proshopwave'); ?>
        </h3>
      </div>

      <?php woocommerce_product_loop_start(); ?>

      <?php
      while ($empty_cart_products->have_posts()) :
        $empty_cart_products->the_post();

        wc_get_template_part('content', 'product');
      endwhile;
      ?>

      <?php woocommerce_product_loop_end(); ?>
    </section>
    <?php wp_reset_postdata(); ?>
  <?php endif; ?>
</div>