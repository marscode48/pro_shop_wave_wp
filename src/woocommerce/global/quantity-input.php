<?php

/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 *
 * @var bool   $readonly If the input should be set to readonly mode.
 * @var string $type     The input type attribute.
 */

defined('ABSPATH') || exit;

$input_id    = $input_id ?? uniqid('quantity_');
$input_name  = $input_name ?? 'quantity';
$input_value = isset($input_value) ? wc_stock_amount($input_value) : 1;
$min_value   = isset($min_value) ? wc_stock_amount($min_value) : 1;
$max_value   = isset($max_value) ? wc_stock_amount($max_value) : '';
$step        = isset($step) ? wc_stock_amount($step) : 1;

?>
<div class="product-quantity js-product-quantity">
  <span class="product-quantity__label product-quantity__label">
    <?php echo esc_html__('数量', 'proshopwave'); ?>
  </span>
  <div class="product-quantity__controls">
    <span class="product-quantity__button product-quantity__button--decrease js-quantity-decrease"></span>
    <div class="product-quantity__input-wrapper">
      <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">
        <?php
        // translators: %s: product name or "quantity".
        $pw_quantity_label = $product ? $product->get_name() : __('数量', 'proshopwave');

        printf(
          esc_html__('%s 個', 'proshopwave'),
          esc_html($pw_quantity_label)
        );
        ?>
      </label>
      <input
        type="number"
        id="<?php echo esc_attr($input_id); ?>"
        class="product-quantity__input input-text qty text"
        name="<?php echo esc_attr($input_name); ?>"
        value="<?php echo esc_attr($input_value); ?>"
        min="<?php echo esc_attr($min_value); ?>"
        <?php if ($max_value) : ?>
        max="<?php echo esc_attr($max_value); ?>"
        <?php endif; ?>
        step="<?php echo esc_attr($step); ?>"
        placeholder=""
        inputmode="numeric"
        autocomplete="off"
        aria-label="<?php echo esc_attr__('商品数量', 'proshopwave'); ?>">
    </div>
    <span class="product-quantity__button product-quantity__button--increase js-quantity-increase"></span>
  </div>
</div>