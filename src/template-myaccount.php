<?php

/**
 * Template Name: WooCommerce My Account (Legacy)
 * Description: 従来版 WooCommerce マイアカウントページ用テンプレート。
 *
 * このテンプレートはマイアカウント固定ページ専用です。
 * WooCommerce ブロック版ではなく myaccount/my-account.php を直接読み込みます。
 *
 * @package PRO_SHOP_WAVE
 */


get_header();

echo '<!-- Debug: My Account page via template-myaccount.php (Shortcode Version) -->';

// WooCommerce マイアカウントページをショートコード経由で出力
if (class_exists('WC_Shortcodes')) {
    echo do_shortcode('[woocommerce_my_account]');
}

get_footer();
