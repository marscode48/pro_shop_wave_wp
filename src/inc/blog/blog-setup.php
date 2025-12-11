<?php

/**
 * PRO SHOP WAVE ブログ初期セットアップ
 *
 * - ブログ用初期カテゴリの自動登録
 * - 初期ブログ投稿の自動登録（1回限り）
 */

// -----------------------------
// ブログ用初期カテゴリの自動登録
// -----------------------------
function proshopwave_register_default_categories()
{
  $categories = [
    [
      'name'        => 'お知らせ',
      'slug'        => 'news',
      'description' => '営業情報、臨時休業、キャンペーンなど公式告知全般',
    ],
    [
      'name'        => 'イベント',
      'slug'        => 'events',
      'description' => 'ドリフトイベント、展示会、サーキット走行会など',
    ],
    [
      'name'        => 'カスタム事例',
      'slug'        => 'custom-builds',
      'description' => '実際のチューニング事例、パーツ取り付け例',
    ],
    [
      'name'        => '商品紹介',
      'slug'        => 'product-info',
      'description' => '新商品の解説、パーツの使い方・効果の紹介',
    ],
    [
      'name'        => 'スタッフブログ',
      'slug'        => 'staff-blog',
      'description' => 'カジュアルな日記、裏話、日常の一コマなど',
    ],
    [
      'name'        => 'ドリフトコラム',
      'slug'        => 'drift-column',
      'description' => '走り屋文化、JDMスタイル、90’sカルチャー解説など',
    ],
  ];

  foreach ($categories as $category) {
    if (!term_exists($category['slug'], 'category')) {
      wp_insert_term(
        $category['name'],
        'category',
        [
          'slug'        => $category['slug'],
          'description' => $category['description'],
        ]
      );
    }
  }
}
add_action('init', 'proshopwave_register_default_categories');

// -----------------------------
// 初期ブログ投稿の自動登録（1回限り）
// -----------------------------
function proshopwave_insert_initial_blog_posts()
{
  if (get_option('proshopwave_blog_posts_inserted')) return;

  // post_exists() を使うために読み込む
  if (!function_exists('post_exists')) {
    // @intelephense-ignore-next-line
    require_once ABSPATH . 'wp-admin/includes/post.php';
  }

  $posts = [
    [
      'post_title'   => '【重要】ゴールデンウィークの営業について',
      'post_content' => 'GW期間中の営業時間と休業日についてご案内します。5月3日〜5日は休業となります。',
      'post_category' => [get_cat_ID('お知らせ')],
    ],
    [
      'post_title'   => '【キャンペーン】期間限定パーツ割引実施中！',
      'post_content' => '5月末まで、対象のドリフトパーツが最大20%OFF。ぜひこの機会に！',
      'post_category' => [get_cat_ID('お知らせ')],
    ],
    [
      'post_title'   => '【臨時休業】イベント出店による休業のお知らせ',
      'post_content' => '5月12日はイベント出店のため、実店舗を臨時休業いたします。',
      'post_category' => [get_cat_ID('お知らせ')],
    ],
    [
      'post_title'   => '【イベント出展】5月某日 横浜ドリフェス参加決定！',
      'post_content' => 'WAVEは「ドリフェス2025 in 横浜」に出展いたします。来場者特典もご用意！',
      'post_category' => [get_cat_ID('イベント')],
    ],
    [
      'post_title'   => '【レポート】名阪サーキット走行会レポート',
      'post_content' => '4月某日に行われた名阪走行会の様子をレポート！大盛況の様子をご覧ください。',
      'post_category' => [get_cat_ID('イベント')],
    ],
    [
      'post_title'   => '【出展予定】6月JDMスタイルミーティング参加予定',
      'post_content' => '6月開催のJDMイベント「JDM STYLE MTG」に参加予定。詳細は後日！',
      'post_category' => [get_cat_ID('イベント')],
    ],
    [
      'post_title'   => '[事例] 180SX × BN SPORTSワイド化 × フルスポット補強',
      'post_content' => 'サーキット走行を想定した180SXのトータルチューン。ワイドフェンダーとロールバーで剛性も見た目もレベルアップ。',
      'post_category' => [get_cat_ID('カスタム事例')],
    ],
    [
      'post_title'   => '[事例] AE86にS2000エンジン換装！ドリ専レストモッド',
      'post_content' => 'NAの高回転フィールを求め、F20Cを搭載。細部までこだわった職人仕事をご覧ください。',
      'post_category' => [get_cat_ID('カスタム事例')],
    ],
    [
      'post_title'   => '[事例] ZN6 86 × フロントオーバーフェンダー × ワンオフマフラー',
      'post_content' => 'ドリフト志向でセッティングしたZN6。独自のエアロ設計とマフラーが好評です。',
      'post_category' => [get_cat_ID('カスタム事例')],
    ],
    [
      'post_title'   => '新発売！「JDM STYLE ステアリング」登場【数量限定】',
      'post_content' => 'ドリフト志向でセッティングしたZN6。独自のエアロ設計とマフラーが好評です。',
      'post_category' => [get_cat_ID('商品紹介')],
    ],
    [
      'post_title'   => 'マフラーサウンド比較レビュー：SR20編',
      'post_content' => 'SR20エンジンに人気のマフラーを装着し、音質とパワーの違いを徹底検証しました。',
      'post_category' => [get_cat_ID('商品紹介')],
    ],
    [
      'post_title'   => '【レビュー】新作リアウィング「WAVE GT-WING」装着レポート',
      'post_content' => '高速安定性とルックスを両立したGTウィングの実力とは？実走レビュー付き。',
      'post_category' => [get_cat_ID('商品紹介')],
    ],
    [
      'post_title'   => '店長の休日：息子と行くミニ四駆大会！',
      'post_content' => 'たまにはクルマから離れて…？スタッフ佐藤が家族で過ごす一日をご紹介。',
      'post_category' => [get_cat_ID('スタッフブログ')],
    ],
    [
      'post_title'   => 'お客様の愛車スナップ【2025年春】',
      'post_content' => 'お店に遊びに来てくれたお客様と愛車たちをピックアップしてご紹介します！',
      'post_category' => [get_cat_ID('スタッフブログ')],
    ],
    [
      'post_title'   => '最近のマイブーム：90年代のカーステ特集！',
      'post_content' => 'カセット派？MD派？今だから語れる懐かしのカーオーディオ事情。',
      'post_category' => [get_cat_ID('スタッフブログ')],
    ],
    [
      'post_title'   => 'なぜ90年代のJDMが今も愛されるのか？',
      'post_content' => 'S13やFC、JZX…。時代を超えて支持される理由を文化的視点から分析。',
      'post_category' => [get_cat_ID('ドリフトコラム')],
    ],
    [
      'post_title'   => '最近のマイブーム：90年代のカーステ特集！',
      'post_content' => '“攻め”と“マナー”のバランス。過去と現在のストリート文化を考察します。',
      'post_category' => [get_cat_ID('ドリフトコラム')],
    ],
    [
      'post_title'   => 'D1以前のドリフトイベント事情【懐かし座談会】',
      'post_content' => '2000年前後の草レースや峠カルチャーを知るスタッフたちの語り。',
      'post_category' => [get_cat_ID('ドリフトコラム')],
    ],
  ];

  foreach ($posts as $post) {
    if (!post_exists($post['post_title'])) {
      wp_insert_post([
        'post_title'    => $post['post_title'],
        'post_content'  => $post['post_content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $post['post_category'],
        'post_type'     => 'post',
      ]);
    }
  }

  update_option('proshopwave_blog_posts_inserted', true);
}
add_action('init', 'proshopwave_insert_initial_blog_posts');
