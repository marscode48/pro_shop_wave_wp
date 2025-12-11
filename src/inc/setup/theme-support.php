<?php

/**
 * Theme setup: 基本的なテーマサポートの登録
 *
 * - タイトルタグ自動出力
 * - アイキャッチ（サムネイル）有効化
 * - HTML5 マークアップ対応
 * - テーマ翻訳ファイルの読み込み
 * - ナビゲーションメニューの登録
 */

if (! function_exists('proshopwave_theme_setup')) {
  function proshopwave_theme_setup()
  {
    // タイトルタグを自動で出力
    add_theme_support('title-tag');

    // アイキャッチ画像有効化
    add_theme_support('post-thumbnails');

    // HTML5サポート
    add_theme_support(
      'html5',
      [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
      ]
    );

    // テーマの翻訳読み込み
    load_theme_textdomain('proshopwave', get_template_directory() . '/languages');

    // ナビゲーションメニューの登録
    register_nav_menus(
      [
        'global' => 'グローバルナビゲーション',
        'footer' => 'フッターナビゲーション',
      ]
    );
  }
}

// after_setup_theme フックでテーマサポートを登録
add_action('after_setup_theme', 'proshopwave_theme_setup');
