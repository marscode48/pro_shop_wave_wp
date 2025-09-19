<?php

/**
 * Template Name: WooCommerce My Account (Block)
 * Description: ブロック版 WooCommerce マイアカウントページ用テンプレート。
 *
 * Gutenberg の「WooCommerce → マイアカウント」ブロックをページ本文に配置して使用します。
 * 従来の [woocommerce_my_account] ショートコードは使用しません。
 *
 * @package PRO_SHOP_WAVE
 */

get_header();

// Breadcrumb navigation
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}
?>

<section class="section section--myaccount" role="region" aria-label="My Account">
    <div class="myaccount__inner">
        <?php
        // ブロックエディタで配置したコンテンツ（マイアカウントブロック）をそのまま出力
        if (function_exists('the_content')) {
            the_content();
        }
        ?>
    </div>
</section>

<?php get_footer();
