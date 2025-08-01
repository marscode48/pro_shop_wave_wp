<?php

/**
 * Template Name: WooCommerce Checkout (Legacy)
 * Description: 従来版 WooCommerce チェックアウトページ用テンプレート。
 *
 * このテンプレートはチェックアウト固定ページ専用です。
 * WooCommerce ブロック版ではなく checkout/form-checkout.php を直接読み込みます。
 *
 * @package PRO_SHOP_WAVE
 */


get_header();

echo '<!-- Debug: Checkout page via template-checkout.php (Shortcode Version) -->';

// WooCommerce チェックアウトページをショートコード経由で出力
if (class_exists('WC_Shortcode_Checkout')) {
  echo do_shortcode('[woocommerce_checkout]');
}

get_footer();
