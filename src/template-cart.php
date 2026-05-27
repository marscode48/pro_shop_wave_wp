<?php

/**
 * Template Name: WooCommerce Cart (Legacy)
 * Description: 従来版 WooCommerce カートページ用テンプレート（ショートコード版）。
 *
 * WooCommerce ブロック版ではなくクラシック型のショートコードで、
 * cart/cart.php などの従来テンプレートと PHP フックを利用します。
 *
 * @package PRO_SHOP_WAVE
 */

get_header();
?>

<section class="section section--cart section--commerce-ui" role="region" aria-label="Cart">
  <div class="cart__inner l-container">
    <?php
    // Breadcrumb navigation
    get_template_part('template-parts/breadcrumb/breadcrumb');
    // WooCommerce カートページをショートコード経由で出力
    if (class_exists('WC_Shortcodes')) {
      echo do_shortcode('[woocommerce_cart]');
    }
    ?>
  </div>
</section>

<?php get_footer();
