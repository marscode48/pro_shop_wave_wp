<?php

/**
 * WooCommerce 共通テンプレート
 *
 * WooCommerceが出力する全ページ（商品一覧、商品詳細、カート、マイアカウント等）で使用されるラッパーテンプレート。
 * テーマのデザインに合わせて、共通のヘッダー・フッターを含めた構成とします。
 * woocommerce.phpは他のテンプレートファイルよりも優先されるため、テーマでwoocommerce/archive-product.phpなどのカスタムテンプレートを上書きすることはできません。これは表示の問題を防ぐためです。
 *
 * @package PRO_SHOP_WAVE
 */
get_header();

if (is_singular('product')) {
  // 商品詳細ページが対象の場合、single-product テンプレートを WooCommerce の専用関数で読み込み
  wc_get_template_part('single-product');
} elseif (is_post_type_archive('product') || is_tax('product_cat') || is_tax('product_tag')) {
  // 商品一覧ページ（アーカイブ）またはカテゴリ・タグアーカイブの場合、archive-product テンプレートを読み込み
  wc_get_template_part('archive-product');
} else {
  // その他の WooCommerce 固有ページ（カート・チェックアウト・マイアカウント等）の共通レイアウト
  woocommerce_content();
}

get_footer();
