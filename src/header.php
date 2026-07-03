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

  <!--  Google Search Console -->
  <meta name="google-site-verification" content="3jOgrn2VauB7cpXdulIAXGFM1zKQgNDYrR6NCHSzw-E" />

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

        <?php
        // WooCommerce マイアカウントへのリンク（ログイン状態でラベルを出し分け）
        $account_url = function_exists('wc_get_page_permalink')
          ? wc_get_page_permalink('myaccount')
          : esc_url(home_url('/my-account/'));
        $is_logged_in = is_user_logged_in();
        $account_label = $is_logged_in
          ? __('My Account', 'proshopwave')
          : __('Log In / Register', 'proshopwave');

        // WooCommerce カートURLへのリンク（多言語・スラッグ変更に追従／WooCommerce無効時はフォールバック）
        $cart_url = function_exists('wc_get_cart_url')
          ? wc_get_cart_url()
          : esc_url(home_url('/cart/'));
        ?>

        <!-- ハンバーガーボタン（SPのみ表示） -->
        <button class="header__toggle" aria-label="<?php echo esc_attr__('メニューを開く', 'proshopwave'); ?>">
          <span></span><span></span><span></span>
        </button>

        <!-- ロゴエリア -->
        <div class="header__logo">
          <?php $html_tag = (is_home() || is_front_page()) ? 'h1' : 'div'; ?>
          <<?php echo $html_tag; ?>>
            <a href="<?php echo esc_url(home_url('/')); ?>">
              <img src="<?php echo get_theme_file_uri('images/logo-pro-shop-wave.svg'); ?>" alt="<?php echo esc_attr__('PRO SHOP WAVE ロゴ', 'proshopwave'); ?>" />
            </a>
          </<?php echo $html_tag; ?>>
        </div>

        <!-- グローバルナビ -->
        <nav class="header__nav">
          <ul class="header__nav-list">
            <li class="header__nav-item header__nav-item--has-children">
              <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Shop', 'proshopwave'); ?></a>
              <button class="header__nav-toggle" aria-haspopup="menu" aria-expanded="false" aria-label="商品カテゴリを開く">
                <span class="header__nav-toggle-icon"></span>
              </button>
              <ul class="header__submenu">
                <li><a href="<?php echo esc_url(get_term_link('parts', 'product_cat')); ?>"><?php esc_html_e('Parts', 'proshopwave'); ?></a></li>
                <li><a href="<?php echo esc_url(get_term_link('apparel-goods', 'product_cat')); ?>"><?php esc_html_e('Apparel & Goods', 'proshopwave'); ?></a></li>
              </ul>
            </li>
            <li class="header__nav-item"><a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'proshopwave'); ?></a></li>
            <li class="header__nav-item"><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About', 'proshopwave'); ?></a></li>
            <li class="header__nav-item"><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact', 'proshopwave'); ?></a></li>
            <li class="header__nav-item"><a href="<?php echo esc_url(home_url('/access/')); ?>"><?php esc_html_e('Access', 'proshopwave'); ?></a></li>

            <!-- アカウント出し分け（SPのみ表示） -->
            <li class="header__nav-item header__nav-account">
              <a href="<?php echo esc_url($account_url); ?>">
                <?php echo esc_html($account_label); ?>
              </a>
            </li>

            <?php /*
            <!-- 多言語切り替え（SPのみ表示 / WPML実装後に有効化） -->
            <li class="header__nav-item header__nav-lang">
              <a href="#" class="is-active">🇯🇵 JP</a>
              <span class="header__lang-separator">|</span>
              <a href="#">🇺🇸 EN</a>
            </li>
            */ ?>

            <!-- ロゴ（SPのみ表示） -->
            <li class="header__nav-item header__logo">
              <a href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo get_theme_file_uri('images/logo-pro-shop-wave.svg'); ?>" alt="<?php echo esc_attr__('PRO SHOP WAVE ロゴ', 'proshopwave'); ?>" />
              </a>
            </li>
          </ul>
        </nav>

        <!-- ユーティリティエリア -->
        <div class="header__utils">

          <div class="header__search">
            <!-- SP用: 検索アイコン -->
            <button class="header__search-toggle" aria-label="検索">
              <i class="fas fa-search"></i>
            </button>

            <!-- PC用：検索フォーム（All検索／ショップ内は自動で商品スコープ） -->
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="header__search-form" role="search">
              <label class="screen-reader-text" for="global-search"><?php echo esc_html__('サイト内検索', 'proshopwave'); ?></label>
              <?php
              $placeholder_text = (function_exists('is_woocommerce') && is_woocommerce())
                ? __('ショップ内検索', 'proshopwave')
                : __('商品・ブログを検索', 'proshopwave');
              ?>
              <input
                id="global-search"
                type="search"
                name="s"
                placeholder="<?php echo esc_attr($placeholder_text); ?>">
              <?php if (function_exists('is_woocommerce') && is_woocommerce()) : ?>
                <!-- post_type が product の場合は archive-product.php に移動-->
                <input type="hidden" name="post_type" value="product">
              <?php endif; ?>
              <button type="submit" aria-label="<?php echo esc_attr__('検索', 'proshopwave'); ?>">
                <i class="fas fa-search" aria-hidden="true"></i>
              </button>
            </form>
          </div>

          <!-- アカウントアイコン（PCのみ表示） -->
          <div class="header__account">
            <a href="<?php echo esc_url($account_url); ?>" class="header__account-link" aria-label="<?php echo esc_attr($account_label); ?>">
              <i class="fas fa-user-circle" aria-hidden="true"></i>
            </a>
          </div>

          <!-- カートアイコン（数量バッジ付き） -->
          <div class="header__cart">
            <a href="<?php echo esc_url($cart_url); ?>" aria-label="<?php echo esc_attr__('カート', 'proshopwave'); ?>">
              <i class="fas fa-shopping-cart" aria-hidden="true"></i>
              <?php
              $cart_count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
              $count_class = $cart_count > 0 ? ' is-active' : '';
              ?>
              <span class="header__cart-count<?php echo esc_attr($count_class); ?>" aria-live="polite" aria-atomic="true"><?php echo esc_html($cart_count); ?></span>
            </a>
          </div>

          <?php /*
          <!-- 多言語切り替え（WPML実装後に有効化） -->
          <div class="header__lang">
            <a href="#" class="is-active">🇯🇵 JP</a>
            <span class="header__lang-separator">|</span>
            <a href="#">🇺🇸 EN</a>
          </div>
          */ ?>
        </div>

      </div>
    </header>

    <!-- SP検索フォーム（All検索／ショップ内は自動で商品スコープ） -->
    <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="search-form-sp" role="search">
      <label class="screen-reader-text" for="sp-search"><?php echo esc_html__('サイト内検索', 'proshopwave'); ?></label>
      <?php
      $sp_placeholder_text = (function_exists('is_woocommerce') && is_woocommerce())
        ? __('ショップ内検索', 'proshopwave')
        : __('サイト内検索（商品・ブログ）', 'proshopwave');
      ?>
      <input
        id="sp-search"
        type="search"
        name="s"
        placeholder="<?php echo esc_attr($sp_placeholder_text); ?>">
      <?php if (function_exists('is_woocommerce') && is_woocommerce()) : ?>
        <!-- post_type が product の場合は archive-product.php に移動-->
        <input type="hidden" name="post_type" value="product">
      <?php endif; ?>
      <button type="submit" aria-label="<?php echo esc_attr__('検索', 'proshopwave'); ?>">
        <i class="fas fa-search" aria-hidden="true"></i>
      </button>
    </form>