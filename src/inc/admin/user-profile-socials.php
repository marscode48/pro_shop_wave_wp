<?php

/**
 * 管理画面: ユーザープロフィール SNS 設定
 *
 * - [ユーザー] > [プロフィール] に SNS 入力欄を追加
 * - 入力された値は get_the_author_meta('<key>') / get_user_meta() で取得可能
 * - get_author_social_url() を使うと URL / @ハンドルを統一フォーマットのURLに正規化
 */

// ==================================================
// 管理画面: ユーザープロフィールに SNS 項目を追加
// ・[ユーザー] > [プロフィール] で各著者が自分のSNSを登録可能にします
// ・保存された値は get_the_author_meta('<key>') で取得できます
//   例) get_the_author_meta('twitter'); get_the_author_meta('instagram');
// ==================================================
add_filter('user_contactmethods', function ($methods) {
  // 既存: 'user_url' (Webサイト) は WordPress デフォルトで存在
  // 追加: 各SNSの入力欄（URL でも @ハンドルでも OK）
  $methods['twitter']   = 'Twitter / X (URL または @ユーザー名)';
  $methods['instagram'] = 'Instagram (URL または @ユーザー名)';
  $methods['facebook']  = 'Facebook（フルURLのみ）';
  $methods['youtube']   = 'YouTube (URL または @ハンドル)';
  return $methods;
}, 10, 1);

// --------------------------------------------------
// Helper: 入力が URL でも "@handle" でも、実URLに正規化して返す
// 使い方: get_author_social_url(get_the_ID(), 'twitter');
// 対応キー: twitter / instagram / facebook / youtube
// --------------------------------------------------
function get_author_social_url($user_id, $service)
{
  $val = trim((string) get_user_meta($user_id, $service, true));
  if ($val === '') return '';

  // --- Facebook はフルURLのみ許可（@ハンドルやドメイン省略は不可）
  if ($service === 'facebook') {
    // http/https 以外は不可
    if (!preg_match('~^https?://~i', $val)) return '';
    // @ を含む誤入力を拒否
    if (strpos($val, '@') !== false) return '';
    // facebook.com ドメイン以外は不可
    if (!preg_match('~^https?://(?:www\.)?facebook\.com/[^\s]+~i', $val)) return '';
    return esc_url_raw($val);
  }

  // 1) 完全なURLならそのまま（ただし "http://@user" のような誤入力は除外）
  if (preg_match('~^https?://~i', $val) && strpos($val, '@') === false) {
    return esc_url_raw($val);
  }

  // 2) 先頭に "twitter.com/..." や "x.com/..." などドメインのみで始まる場合は https:// を補う
  if (preg_match('~^(?:www\.)?(twitter\.com|x\.com|instagram\.com|www\.instagram\.com|facebook\.com|www\.facebook\.com|youtube\.com|www\.youtube\.com)/~i', $val)) {
    return esc_url_raw('https://' . ltrim($val, '/'));
  }

  // 3) "@handle" 形式、または "http://@handle" のような誤URL → ハンドルとして扱う
  //    "http(s)://@..." は esc_url() 経由で "http://@user" になりがちなのでここで救済
  $handle = $val;
  // "http://@user" / "https://@user" を削除
  $handle = preg_replace('~^https?://@~i', '', $handle);
  // 先頭の @ を削除
  $handle = ltrim($handle, '@');

  // 4) サービス毎のベースURL
  $bases = [
    'twitter'   => 'https://x.com/%s',
    'instagram' => 'https://www.instagram.com/%s',
    'facebook'  => 'https://www.facebook.com/%s',
    'youtube'   => 'https://www.youtube.com/@%s',
  ];

  if (!isset($bases[$service])) return '';

  // 5) ハンドルをURLエンコードして整形
  return sprintf($bases[$service], rawurlencode($handle));
}
