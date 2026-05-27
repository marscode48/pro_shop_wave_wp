<?php

/**
 * Template Name: WooCommerce Checkout (Legacy)
 * Description: 従来版 WooCommerce チェックアウトページ用テンプレート（ショートコード版）。
 *
 * WooCommerce ブロック版ではなくクラシック型のショートコードで、
 * checkout/form-checkout.php などの従来テンプレートと PHP フックを利用します。
 *
 * @package PRO_SHOP_WAVE
 */

get_header();
?>

<section class="section section--checkout section--commerce-ui" role="region" aria-label="Checkout">
  <div class="checkout__inner l-container">
    <?php
    // Breadcrumb navigation
    get_template_part('template-parts/breadcrumb/breadcrumb');
    // WooCommerce チェックアウトページをショートコード経由で出力
    if (class_exists('WC_Shortcodes')) {
      echo do_shortcode('[woocommerce_checkout]');
    }
    ?>
  </div>
</section>

<?php get_footer();
