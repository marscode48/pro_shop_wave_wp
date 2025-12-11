<?php

/**
 * クエリ関連のユーティリティ関数
 *
 * - URLクエリやクエリ変数から「スラッグ」を安全に取得するためのヘルパー。
 * - 主に WooCommerce / ブログの一覧ページで、カテゴリ・タグ・ブランドなどの
 *   絞り込みに利用することを想定。
 */

/**
 * 指定キーのクエリ値を「スラッグ」として安全に取得するヘルパー。
 *
 * 優先順:
 *   1. get_query_var($key)
 *   2. $_GET[$key]
 *
 * - 文字列以外の値は無視（配列などは先頭要素を文字列化）
 * - 前後の空白をトリム
 * - sanitize_title() でスラッグとして安全な形式に整形
 *
 * @param string      $key      クエリキー（例: 'product_cat', 'product_brand', 'tag' など）
 * @param string|null $default  正常に取得できなかった場合のデフォルト値
 * @return string               サニタイズ済みのスラッグ（なければ $default）
 */
if (! function_exists('proshopwave_get_query_slug')) {
  function proshopwave_get_query_slug($key, $default = null)
  {
    $raw = get_query_var($key);

    // get_query_var で取得できなければ $_GET も見る
    if (empty($raw) && isset($_GET[$key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      $raw = wp_unslash($_GET[$key]);
    }

    // 配列で来た場合は先頭要素だけを見る
    if (is_array($raw)) {
      $raw = reset($raw);
    }

    // 文字列以外は無効とみなす
    if (!is_string($raw)) {
      return $default !== null ? (string) $default : '';
    }

    $raw = trim($raw);
    if ($raw === '') {
      return $default !== null ? (string) $default : '';
    }

    // スラッグとして安全な形に整形
    $slug = sanitize_title($raw);

    if ($slug === '') {
      return $default !== null ? (string) $default : '';
    }

    return $slug;
  }
}
