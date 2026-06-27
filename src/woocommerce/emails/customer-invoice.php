<?php

/**
 * Customer invoice email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-invoice.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

use Automattic\WooCommerce\Enums\OrderStatus;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

if (! defined('ABSPATH')) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled('email_improvements');
$is_estimate_shipping_order = function_exists('proshopwave_order_has_estimate_shipping_item') && proshopwave_order_has_estimate_shipping_item($order);

/**
 * Executes the e-mail header.
 *
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<p>
	<?php
	if (! empty($order->get_billing_first_name())) {
		/* translators: %s: Customer first name */
		printf(esc_html__('Hi %s,', 'woocommerce'), esc_html($order->get_billing_first_name()));
	} else {
		printf(esc_html__('Hi,', 'woocommerce'));
	}
	?>
</p>
<?php if ($order->needs_payment()) { ?>
	<?php if ($order->has_status(OrderStatus::FAILED)) : ?>
		<p>
			<?php
			printf(
				wp_kses(
					/* translators: %1$s Site title, %2$s Order pay link */
					__('%1$sでのお支払いが完了しませんでした。以下の注文内容をご確認のうえ、再度お支払いをお試しください: %2$s', 'proshopwave'),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				esc_html(get_bloginfo('name', 'display')),
				'<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . esc_html__('この注文の支払いへ進む', 'proshopwave') . '</a>'
			);
			?>
		</p>
	<?php elseif ($is_estimate_shipping_order) : ?>
		<p><?php esc_html_e('ご注文内容を確認し、送料を含めたお支払い総額が確定しました。', 'proshopwave'); ?></p>
		<p><?php esc_html_e('以下の注文内容をご確認のうえ、銀行振込にてお支払いをお願いいたします。', 'proshopwave'); ?></p>
	<?php else : ?>
		<p>
			<?php
			printf(
				wp_kses(
					/* translators: %1$s Site title, %2$s Order pay link */
					__('%1$sでご注文を承りました。以下の注文内容をご確認のうえ、お支払いへお進みください: %2$s', 'proshopwave'),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				esc_html(get_bloginfo('name', 'display')),
				'<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . esc_html__('この注文の支払いへ進む', 'proshopwave') . '</a>'
			);
			?>
		</p>
	<?php endif; ?>

<?php } else { ?>
	<p>
		<?php
		/* translators: %s Order date */
		printf(esc_html__('%s のご注文内容を以下にお知らせします。', 'proshopwave'), esc_html(wc_format_datetime($order->get_date_created())));
		?>
	</p>
<?php
}
?>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php

/**
 * Hook for the woocommerce_email_order_details.
 *
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Hook for the woocommerce_email_order_meta.
 *
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/**
 * Hook for woocommerce_email_customer_details.
 *
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
	echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' : '';
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/**
 * Executes the email footer.
 *
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
