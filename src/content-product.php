<?php

/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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
 */

defined('ABSPATH') || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if (! is_a($product, WC_Product::class) || ! $product->is_visible()) {
  return;
}
?>
<li <?php wc_product_class('', $product); ?>>
  <?php
  /**
   * Hook: woocommerce_before_shop_loop_item.
   *
   * @hooked woocommerce_template_loop_product_link_open - 10
   */
  do_action('woocommerce_before_shop_loop_item');

  /**
   * Hook: woocommerce_before_shop_loop_item_title.
   *
   * @hooked woocommerce_show_product_loop_sale_flash - 10
   * (Removed: @hooked woocommerce_template_loop_product_thumbnail - 10)
   */
  remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
  do_action('woocommerce_before_shop_loop_item_title');
  ?>
  <div class="product-card__image">
    <a href="<?php the_permalink(); ?>" class="product-card__image-link">
      <?php echo woocommerce_get_product_thumbnail(); ?>
    </a>
  </div>
  <div class="product-card__body">
    <div class="product-card__topline">
      <div class="product-card__category">
        <?php
        // 商品カードではカテゴリーを「一番子側」のカテゴリー1件だけ表示する。
        // WooCommerce標準の wc_get_product_category_list() は、商品に付いている親・子カテゴリーをまとめて出力するため、
        // 例: 「サスペンション, パーツ」のように複数表示される。
        // 一覧カードでは表示を簡潔にするため、商品に付いているカテゴリーのうち「一番子側」のカテゴリーを優先して表示する。
        $product_categories = get_the_terms($product->get_id(), 'product_cat');

        if (! is_wp_error($product_categories) && ! empty($product_categories)) {

          $display_category = null;

          // 商品に付いているカテゴリー同士を比較し、
          // 「他のカテゴリーの親になっていないカテゴリー」= 一番子側のカテゴリーを表示対象にする。
          foreach ($product_categories as $category) {
            $has_child_in_product = false;

            foreach ($product_categories as $compare_category) {
              if ((int) $compare_category->parent === (int) $category->term_id) {
                $has_child_in_product = true;
                break;
              }
            }

            if (! $has_child_in_product) {
              $display_category = $category;
              break;
            }
          }

          // 念のため表示対象が決まらなかった場合は、先頭のカテゴリーを表示する。
          if (! $display_category) {
            $display_category = reset($product_categories);
          }

          $category_link = get_term_link($display_category);

          if (! is_wp_error($category_link)) :
        ?>
            <a href="<?php echo esc_url($category_link); ?>" rel="tag">
              <?php echo esc_html($display_category->name); ?>
            </a>
        <?php
          endif;
        }
        ?>
      </div>
      <?php
      // -----------------------------
      // ブランド表示（型式は表示しない）
      // ルール: 「車種（子）」をすべて表示。存在しなければ「メーカー（親）」を表示。
      // 対応タクソノミー: product_brand or pa_brand（存在する方を使用）
      // ※ 商品に型式（孫）が付いている場合は、その親である「車種」を表示します。
      // -----------------------------
      $brand_tax = taxonomy_exists('product_brand') ? 'product_brand' : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');

      if ($brand_tax) {
        $terms = get_the_terms($product->get_id(), $brand_tax);

        if (! is_wp_error($terms) && ! empty($terms)) {

          // term_id => WP_Term の辞書（親たどり用）
          $dict = [];
          foreach ($terms as $t) {
            $dict[$t->term_id] = $t;
          }

          // タームの深さを計算（親を辿る）
          $depth_of = function ($term) use ($dict) {
            $d = 0;
            $p = $term->parent ?? 0;

            while ($p && isset($dict[$p])) {
              $d++;
              $p = $dict[$p]->parent ?? 0;
            }
            return $d; // 0=親（メーカー）, 1=子（車種）, 2+=孫（型式…）
          };

          $display_terms = []; // 表示対象（term_id => WP_Term）

          // 1) 型式（孫）があれば、その親＝車種をすべて表示対象に
          foreach ($terms as $t) {
            if ($depth_of($t) >= 2) {
              $parent_id = $t->parent ?? 0;

              if ($parent_id && isset($dict[$parent_id])) {
                $display_terms[$parent_id] = $dict[$parent_id];
              }
            }
          }

          // 2) 車種（子）が直接付いていれば、それもすべて表示対象に追加
          foreach ($terms as $t) {
            if ($depth_of($t) === 1) {
              $display_terms[$t->term_id] = $t;
            }
          }

          // 3) 車種が無ければ メーカー（親）を表示
          if (empty($display_terms)) {
            foreach ($terms as $t) {
              if ($depth_of($t) === 0) {
                $display_terms[$t->term_id] = $t;
                break;
              }
            }
          }

          if (! empty($display_terms)) :
            $display_items = [];

            foreach ($display_terms as $display_term) {
              $term_link = get_term_link($display_term);

              if (is_wp_error($term_link)) {
                continue;
              }

              $display_items[] = sprintf(
                '<a href="%s" class="product-card__brand-link">%s</a>',
                esc_url($term_link),
                esc_html($display_term->name)
              );
            }

            if (! empty($display_items)) :
      ?>
              <div class="product-card__brand" title="<?php echo esc_attr(wp_strip_all_tags(implode(' / ', wp_list_pluck($display_terms, 'name')))); ?>">
                <?php echo wp_kses_post(implode('<span class="product-card__brand-separator"> / </span>', $display_items)); ?>
              </div>
      <?php
            endif;
          endif;
        }
      }
      ?>
    </div>
    <?php
    /**
     * Hook: woocommerce_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_product_title - 10
     */
    do_action('woocommerce_shop_loop_item_title');

    /**
     * Hook: woocommerce_after_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_rating - 5
     * @hooked woocommerce_template_loop_price - 10
     */
    do_action('woocommerce_after_shop_loop_item_title');
    ?>
    <div class="product-card__more">
      <a href="<?php the_permalink(); ?>" class="product-card__more-link">
        <?php echo esc_html__('More', 'proshopwave'); ?><i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
    <?php
    /**
     * Hook: woocommerce_after_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_close - 5
     * @hooked woocommerce_template_loop_add_to_cart - 10
     */
    do_action('woocommerce_after_shop_loop_item');
    ?>

  </div>
</li>