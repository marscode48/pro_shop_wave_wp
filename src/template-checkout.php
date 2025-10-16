<?php

/**
 * Template Name: WooCommerce Checkout (Block)
 * Description: ブロック版 WooCommerce チェックアウトページ用テンプレート。
 *
 * Gutenberg の「WooCommerce → チェックアウト」ブロックをページ本文に配置して使用します。
 * 従来の [woocommerce_checkout] ショートコードは使用しません。
 *
 * @package PRO_SHOP_WAVE
 */

get_header();

// Breadcrumb navigation
if (function_exists('woocommerce_breadcrumb')) {
  woocommerce_breadcrumb();
}
?>

<section class="section section--checkout" role="region" aria-label="Checkout">
  <div class="checkout__inner">
    <?php
    // ブロックエディタで配置したコンテンツ（チェックアウトブロック）をそのまま出力
    if (function_exists('the_content')) {
      the_content();
    }
    ?>
  </div>
</section>

<?php get_footer();
