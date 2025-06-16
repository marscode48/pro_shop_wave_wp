<?php
/**
 * The template for displaying the header
 *
 * @package PRO_SHOP_WAVE
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- SEO / OGP -->
  <meta name="description" content="<?php bloginfo('description'); ?>">
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?php echo wp_get_document_title(); ?>" />
  <meta property="og:description" content="<?php bloginfo('description'); ?>" />
  <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
  <meta property="og:image" content="<?php echo esc_url(get_theme_file_uri('images/ogp-1200x630.jpg')); ?>" />
  <meta property="og:site_name" content="<?php bloginfo('name'); ?>" />
  <meta property="og:locale" content="ja_JP" />

  <!-- Canonical -->
  <link rel="canonical" href="<?php echo esc_url(get_permalink()); ?>" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">

  <!-- Favicon / PWA -->
  <link rel="icon" type="image/png" href="<?php echo esc_url(get_theme_file_uri('images/favicon-96x96.png')); ?>" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_theme_file_uri('images/favicon.svg')); ?>" />
  <link rel="shortcut icon" href="<?php echo esc_url(get_theme_file_uri('images/favicon.ico')); ?>" />
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_theme_file_uri('images/apple-touch-icon.png')); ?>" />
  <meta name="apple-mobile-web-app-title" content="WAVE" />
  <link rel="manifest" href="<?php echo esc_url(get_theme_file_uri('images/site.webmanifest')); ?>" />

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <!-- ローディング中に表示されるスピナー（orbitアニメーション） -->
  <div class="orbit-spinner">
    <div class="orbit"></div>
    <div class="orbit"></div>
    <div class="orbit"></div>
  </div>

  <!-- Pace.js により読み込みが完了した後に表示されるメインコンテンツ -->
  <div id="page-content">
    <!-- メニューを開いたときに背景を暗くぼかす -->
    <div class="nav-overlay"></div>
    <!-- Header -->
    <div class="nav-trigger scroll-indicator-trigger"></div>
    <header class="header">
      <div class="header__inner">

        <!-- ハンバーガーボタン（SPのみ表示） -->
        <button class="header__toggle" aria-label="メニューを開く">
          <span></span><span></span><span></span>
        </button>

        <!-- ロゴエリア -->
        <div class="header__logo">
        <?php $html_tag = (is_home() || is_front_page()) ? 'h1' : 'div'; ?>
          <<?php echo $html_tag; ?>>
            <a href="<?php echo esc_url(home_url('/')); ?>">
              <img src="<?php echo get_theme_file_uri('images/logo_pro-shop-wave.svg'); ?>" alt="PRO SHOP WAVE ロゴ" />
            </a>
          </<?php echo $html_tag; ?>>
        </div>

        <!-- グローバルナビ -->
        <nav class="header__nav">
          <ul class="header__nav-list">
            <li class="header__nav-item"><a href="/parts/">Parts</a></li>
            <li class="header__nav-item"><a href="/apparel/">Apparel</a></li>
            <li class="header__nav-item"><a href="/blog/">Blog</a></li>
            <li class="header__nav-item"><a href="/contact/">Contact</a></li>
            <li class="header__nav-item"><a href="/access/">Access</a></li>

            <!-- 多言語切り替え（SPのみ表示） -->
            <li class="header__nav-item header__nav-lang">
              <a href="#" class="is-active">🇯🇵 JP</a>
              <span class="header__lang-separator">|</span>
              <a href="#">🇺🇸 EN</a>
            </li>

            <!-- ロゴ（SPのみ表示） -->
            <li class="header__nav-item header__logo">
              <a href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo get_theme_file_uri('images/logo_pro-shop-wave.svg'); ?>" alt="PRO SHOP WAVE ロゴ" />
              </a>
            </li>
          </ul>
        </nav>

        <!-- ユーティリティエリア -->
        <div class="header__utils">

          <!-- SP用: 検索アイコン -->
          <button class="header__search-toggle" aria-label="検索">
            <i class="fas fa-search"></i>
          </button>

          <!-- PC用：検索フォーム -->
          <form action="/search" method="get" class="header__search-form">
            <input type="text" name="s" placeholder="パーツやブログを検索">
            <button type="submit" aria-label="検索"><i class="fas fa-search"></i></button>
          </form>

          <!-- カートアイコン -->
          <div class="header__cart">
            <a href="/cart" aria-label="カート">
              <i class="fas fa-shopping-cart"></i>
            </a>
          </div>

          <!-- 多言語切り替え -->
          <div class="header__lang">
            <a href="#" class="is-active">🇯🇵 JP</a>
            <span class="header__lang-separator">|</span>
            <a href="#">🇺🇸 EN</a>
          </div>
        </div>

      </div>
    </header>

    <!-- SP検索フォーム -->
    <form action="/search" method="get" class="search-form-sp">
      <input type="text" name="s" placeholder="キーワードを検索">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>