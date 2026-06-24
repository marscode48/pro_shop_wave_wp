<?php

/**
 * テーマの CSS / JS 読み込み関連
 *
 * - フロント全体のスタイル・スクリプト読み込み
 * - WooCommerce 専用スタイルの条件読み込み
 *
 * @package PRO_SHOP_WAVE
 */

// -----------------------------
// CSS・JS の読み込み
// -----------------------------
function proshopwave_enqueue_assets()
{
  // WooCommerceがjQueryに依存しているため、削除は行わない（GSAPなどはVanilla JSで対応）
  // wp_deregister_script('jquery');

  // ローディングCSS（サイトのフロントページのみ適用）
  if (is_front_page()) {
    wp_enqueue_style('loader', get_theme_file_uri('css/loader.css'), [], false, 'all');
  }

  // リセットCSS
  wp_enqueue_style('ress', 'https://unpkg.com/ress@5.0.2/dist/ress.min.css', [], null);

  // Google Fonts
  wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Noto+Sans+JP:wght@400;700&display=swap', [], null);

  // Font Awesome
  wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', [], null);

  // Swiper
  wp_enqueue_style('swiper', 'https://unpkg.com/swiper@11/swiper.min.css', [], null);

  // ペースローダーは最初に実行されるべきなので defer しない
  wp_enqueue_script('pace', 'https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js', [], null, false);

  // Vivus
  wp_enqueue_script('vivus', 'https://cdn.jsdelivr.net/npm/vivus@latest/dist/vivus.min.js', [], null, ['strategy' => 'defer', 'in_footer' => true]);

  // GSAP
  wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', [], null, ['strategy' => 'defer', 'in_footer' => true]);

  // メインCSS（テーマ直下 style.css）
  wp_enqueue_style('theme-style', get_stylesheet_uri(), [], @filemtime(get_theme_file_path('style.css')));

  // メインJS（modules構成）
  wp_enqueue_script('scroll-polyfill', get_theme_file_uri('/js/vendors/scroll-polyfill.js'), [], null, ['strategy' => 'defer', 'in_footer' => true]);
  wp_enqueue_script('main-js', get_theme_file_uri('/js/main.js'), [], filemtime(get_theme_file_path('/js/main.js')), ['strategy' => 'defer', 'in_footer' => true]);
}
add_action('wp_enqueue_scripts', 'proshopwave_enqueue_assets');


// ==============================
// WooCommerce 専用スタイルの条件読み込み（効率化版）
// ・クラシック版: カート / チェックアウト / マイアカウント / 商品一覧 / 商品詳細は Legacy CSS
// ・非WooCommerceページ（ホーム/ブログ等）では原則読み込まない
// ==============================
function proshopwave_enqueue_woocommerce_styles()
{
  // WooCommerce が無効な環境では処理しない
  if (! function_exists('is_woocommerce')) {
    return;
  }

  // 子テーマ優先のパス/URI
  $base_uri = get_stylesheet_directory_uri();
  $base_dir = get_stylesheet_directory();

  $path_legacy = $base_dir . '/css/woocommerce/legacy/woocommerce-legacy.css';

  // --- 判定: チェックアウト系エンドポイント
  $is_thankyou = function_exists('is_order_received_page') && is_order_received_page();
  $is_order_pay = function_exists('is_checkout') && function_exists('is_wc_endpoint_url') && is_checkout() && is_wc_endpoint_url('order-pay');

  // --- クラシック版（カート / チェックアウト / マイアカウント / Thank You / Order Pay）
  // Cart / Checkout はブロック版からショートコード版へ移行したため、Legacy CSS を読み込む。
  if (is_cart() || is_checkout() || is_account_page() || $is_thankyou || $is_order_pay) {
    wp_enqueue_style(
      'proshopwave-woocommerce-legacy',
      $base_uri . '/css/woocommerce/legacy/woocommerce-legacy.css',
      [],
      file_exists($path_legacy) ? filemtime($path_legacy) : null
    );
    return;
  }

  // --- 商品表示系の WooCommerce ページ（商品一覧 / 商品詳細 / 商品カテゴリなど）
  // is_woocommerce() はカート・チェックアウト・マイアカウントを拾わないため、上の条件とは分けて判定する。
  if (is_woocommerce()) {
    wp_enqueue_style(
      'proshopwave-woocommerce-legacy',
      $base_uri . '/css/woocommerce/legacy/woocommerce-legacy.css',
      [],
      file_exists($path_legacy) ? filemtime($path_legacy) : null
    );
  }
  // それ以外（非WooCommerceページ）は読み込まない → パフォーマンス最適化
}

add_action('wp_enqueue_scripts', 'proshopwave_enqueue_woocommerce_styles', 20);

// ==============================
// WooCommerce カートフラグメントの読み込み
// ・カートページで数量変更 / 商品削除を行った際に、ヘッダーのカート数量バッジを更新するために使用
// ・wc_fragment_refresh を受け取る WooCommerce 側の cart-fragments.js を明示的に読み込む
// ==============================
function proshopwave_enqueue_woocommerce_cart_fragments()
{
  // WooCommerce が無効な環境では処理しない
  if (! function_exists('is_cart')) {
    return;
  }

  // カートページのみ、ヘッダーのカート数量バッジ更新に必要なフラグメントJSを読み込む
  if (is_cart()) {
    wp_enqueue_script('wc-cart-fragments');
  }
}
add_action('wp_enqueue_scripts', 'proshopwave_enqueue_woocommerce_cart_fragments', 30);
