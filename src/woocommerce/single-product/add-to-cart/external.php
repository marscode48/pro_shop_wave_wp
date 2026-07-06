<?php

/**
 * External product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/external.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_add_to_cart_form'); ?>

<div class="proshopwave-external-product-notice" role="note">
	<p class="proshopwave-external-product-notice__text">
		<?php echo esc_html__('この商品はBASEショップでの販売となります。', 'proshopwave'); ?>
	</p>
	<p class="proshopwave-external-product-notice__text">
		<?php echo esc_html__('ボタンを押すと、外部サイトのBASE商品ページへ移動します。', 'proshopwave'); ?>
	</p>
	<p class="proshopwave-external-product-notice__text">
		<?php echo esc_html__('在庫状況・発送についてはBASE商品ページをご確認ください。', 'proshopwave'); ?>
	</p>
</div>

<form class="cart cart--external-product" action="<?php echo esc_url($product_url); ?>" method="get" target="_blank" rel="noopener noreferrer">
	<?php do_action('woocommerce_before_add_to_cart_button'); ?>

	<button type="submit" class="single_add_to_cart_button button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"><?php echo esc_html($button_text); ?></button>

	<?php wc_query_string_form_fields($product_url); ?>

	<?php do_action('woocommerce_after_add_to_cart_button'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>