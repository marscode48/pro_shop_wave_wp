

<?php

/**
 * ナビゲーションメニュー関連の設定・ヘルパー
 *
 * メニューの登録や、将来的なメニュー用ヘルパー関数は
 * このファイルに集約して管理します。
 *
 * @package PRO_SHOP_WAVE
 */

// -----------------------------
// テーマのナビゲーションメニュー登録
// -----------------------------
if (! function_exists('proshopwave_register_menus')) {
  function proshopwave_register_menus()
  {
    // 既存テーマで使用中のメニュー登録がある場合は、
    // 必要に応じてここへ集約していきます。
    // 例:
    // register_nav_menus([
    //   'global' => 'グローバルナビゲーション',
    //   'footer' => 'フッターメニュー',
    //   'drawer' => 'ドロワーメニュー',
    // ]);
  }
}
add_action('after_setup_theme', 'proshopwave_register_menus');
