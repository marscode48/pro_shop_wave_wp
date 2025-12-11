<?php

/**
 * PRO SHOP WAVE テーマ関数
 *
 * @package PRO_SHOP_WAVE
 */

// テーマの基本セットアップ（テーマサポート / メニュー登録 など）
require_once get_theme_file_path('inc/setup/theme-support.php');

// テーマのCSS・JS読み込み（フロント資産のenqueue処理）
require_once get_theme_file_path('inc/setup/enqueue.php');

// テーマのナビゲーションメニュー関連の設定
require_once get_theme_file_path('inc/setup/menus.php');

// ブログ用初期カテゴリ・初期投稿の登録
require_once get_theme_file_path('inc/blog/blog-setup.php');

// WooCommerce 関連のサポート・拡張
require_once get_theme_file_path('inc/woocommerce/wc-support.php');

// 管理画面: ユーザープロフィールのSNS設定
require_once get_theme_file_path('inc/admin/user-profile-socials.php');

// Contact Form 7 関連の調整
require_once get_theme_file_path('inc/admin/contact-form7.php');

// クエリ関連のヘルパー関数群
require_once get_theme_file_path('inc/helpers/query.php');

// 出力・アセット関連の微調整
require_once get_theme_file_path('inc/helpers/assets.php');
