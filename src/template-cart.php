<?php

/**
 * Template Name: WooCommerce Cart (Block)
 * Description: ブロック版 WooCommerce カートページ用テンプレート。
 *
 * Gutenberg の「WooCommerce → カート」ブロックをページ本文に配置して使用します。
 * 従来の [woocommerce_cart] ショートコードは使用しません。
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

    // ブロックエディタで配置したコンテンツ（カートブロック）をそのまま出力
    if (function_exists('the_content')) {
      the_content();
    }
    ?>
  </div>
</section>

<?php get_footer();
