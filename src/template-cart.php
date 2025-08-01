<?php

/**
 * Template Name: WooCommerce Cart (Legacy)
 * Description: 従来版 WooCommerce カートページ用テンプレート。
 *
 * このテンプレートはカート固定ページ専用です。
 * WooCommerce ブロック版ではなく cart/cart.php を直接読み込みます。
 * このテンプレートはショートコード版で従来版カートを読み込みます。
 *
 * @package PRO_SHOP_WAVE
 */

get_header();

echo '<!-- Debug: Cart page via page-cart.php (Shortcode Version) -->';

// WooCommerce カートショートコードをショートコード経由で出力
if (function_exists('do_shortcode')) {
    echo do_shortcode('[woocommerce_cart]');
}

get_footer();
