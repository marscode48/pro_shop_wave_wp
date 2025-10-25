<?php

/**
 * Template Name: WooCommerce My Account (Legacy)
 * Description: 従来版 WooCommerce マイアカウントページ用テンプレート（ショートコード版）。
 * このテンプレートはマイアカウント固定ページ専用です。
 * WooCommerce ブロック版ではなくクラシック型のショートコードで、
 * myaccount/my-account.php を直接読み込みます。
 *
 * @package PRO_SHOP_WAVE
 */


get_header();


?>

<section class="section section--myaccount" role="region" aria-label="my-account">
  <div class="myaccount__inner l-container">
    <?php
    // Breadcrumb navigation
    get_template_part('template-parts/breadcrumb/breadcrumb');

    // WooCommerce マイアカウントページをショートコード経由で出力
    if (class_exists('WC_Shortcodes')) {
      echo do_shortcode('[woocommerce_my_account]');
    }
    ?>
  </div>
</section>

<?php get_footer();
