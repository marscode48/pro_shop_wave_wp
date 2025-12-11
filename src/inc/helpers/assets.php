<?php

/**
 * 出力・アセット関連の微調整
 *
 * - scriptタグに type="module" を付与
 * - 2560px超え画像の自動縮小を無効化
 */

// -----------------------------
// scriptタグに type="module" を追加
// -----------------------------
// main.js など ES Modules として読み込みたいスクリプトに対して、
// scriptタグへ type="module" を付与する。
function proshopwave_add_type_attribute($tag, $handle, $src)
{
  // type="module" を付与したいスクリプトのハンドル名一覧
  $module_scripts = ['main-js'];

  if (in_array($handle, $module_scripts, true)) {
    return '<script type="module" src="' . esc_url($src) . '"></script>';
  }

  return $tag;
}
add_filter('script_loader_tag', 'proshopwave_add_type_attribute', 10, 3);

// -----------------------------
// 2560px超え画像を縮小させない
// -----------------------------
// WordPress 5.3以降に追加された大画像の自動縮小機能を無効化し、
// 元の解像度のままアップロードできるようにする。
add_filter('big_image_size_threshold', '__return_false');
