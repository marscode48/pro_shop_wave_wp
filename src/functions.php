<?php
/**
 * PRO SHOP WAVE テーマ関数
 */

// -----------------------------
// タイトルタグ、サムネイル画像を出力
// -----------------------------
function proshopwave_theme_setup() {
  // タイトルタグを自動で出力
  add_theme_support('title-tag');

  // アイキャッチ画像有効化
  add_theme_support('post-thumbnails');

  // HTML5サポート
  add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

  // ナビゲーションメニューの登録
  register_nav_menus([
    'global' => 'グローバルナビゲーション',
    'footer' => 'フッターナビゲーション',
  ]);
}
add_action('after_setup_theme', 'proshopwave_theme_setup');

// -----------------------------
// CSS・JS の読み込み
// -----------------------------
function proshopwave_enqueue_assets() {
  // デフォルトのjQueryは不要なため削除（Vanilla JS + GSAP構成のため）
  wp_deregister_script('jquery');

  // ローディングCSS
  if(is_home() || is_front_page()) {
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

// -----------------------------
// scriptタグに type="module" を追加
// -----------------------------
function add_type_attribute($tag, $handle, $src) {
  $module_scripts = ['main-js'];
  if (in_array($handle, $module_scripts, true)) {
    return '<script type="module" src="' . esc_url($src) . '"></script>';
  }
  return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute', 10, 3);

// -----------------------------
// WPML 対応言語切り替え対応（必要に応じて）
// -----------------------------
// WPMLが有効な場合は、テンプレートで do_action('wpml_add_language_selector') などを使えます。