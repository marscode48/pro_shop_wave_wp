<?php

/**
 * PRO SHOP WAVE テーマ関数
 *
 * @package PRO_SHOP_WAVE
 */

// -----------------------------
// タイトルタグ、サムネイル画像を出力
// -----------------------------
function proshopwave_theme_setup()
{
  // タイトルタグを自動で出力
  add_theme_support('title-tag');

  // アイキャッチ画像有効化
  add_theme_support('post-thumbnails');

  // HTML5サポート
  add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

  // テーマの翻訳読み込み
  load_theme_textdomain('proshopwave', get_template_directory() . '/languages');

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
function proshopwave_enqueue_assets()
{
  // WooCommerceがjQueryに依存しているため、削除は行わない（GSAPなどはVanilla JSで対応）
  // wp_deregister_script('jquery');

  // ローディングCSS（サイトのフロントページのみ適用）
  if (is_front_page()) {
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


// ==============================
// WooCommerce 専用スタイルの条件読み込み（効率化版）
// ・ブロック版: カート / チェックアウト のみ Blocks CSS
// ・従来版: WooCommerce のその他ページ（商品一覧/詳細/カテゴリ/マイアカウント 等）は Legacy CSS
// ・非WooCommerceページ（ホーム/ブログ等）では何も読み込まない
// ==============================
function proshopwave_enqueue_woocommerce_styles() {
  // WooCommerce が無効な環境では処理しない
  if ( ! function_exists( 'is_woocommerce' ) ) {
    return;
  }

  // 子テーマ優先のパス/URI
  $base_uri = get_stylesheet_directory_uri();
  $base_dir = get_stylesheet_directory();

  $path_blocks = $base_dir . '/css/woocommerce/blocks/woocommerce-blocks.css';
  $path_legacy = $base_dir . '/css/woocommerce/legacy/woocommerce-legacy.css';

  // --- 判定: チェックアウト系エンドポイント
  $is_thankyou = function_exists('is_order_received_page') && is_order_received_page();
  $is_order_pay = function_exists('is_checkout') && function_exists('is_wc_endpoint_url') && is_checkout() && is_wc_endpoint_url('order-pay');

  // --- ブロック版（カート / 通常のチェックアウト本体のみ）
  // ※Thank You(注文受領)やOrder Payなどの従来テンプレは除外
  if ( is_cart() || ( is_checkout() && ! $is_thankyou && ! $is_order_pay ) ) {
    wp_enqueue_style(
      'proshopwave-woocommerce-blocks',
      $base_uri . '/css/woocommerce/blocks/woocommerce-blocks.css',
      [],
      file_exists( $path_blocks ) ? filemtime( $path_blocks ) : null
    );
    return; // ブロックCSSを読み込んだら終了（Legacyは不要）
  }

  // --- ブログ記事ページ（投稿タイプ: post のシングルページ）では WooCommerce Blocks のCSSを読み込む
  if ( is_singular( 'post' ) ) {
    wp_enqueue_style(
      'proshopwave-woocommerce-blocks',
      $base_uri . '/css/woocommerce/blocks/woocommerce-blocks.css',
      [],
      file_exists( $path_blocks ) ? filemtime( $path_blocks ) : null
    );
    return;
  }

  // --- 従来版（その他の WooCommerce ページ + マイアカウント + Thank You 等の従来テンプレ）
  if ( is_woocommerce() || is_account_page() || $is_thankyou || $is_order_pay ) {
    wp_enqueue_style(
      'proshopwave-woocommerce-legacy',
      $base_uri . '/css/woocommerce/legacy/woocommerce-legacy.css',
      [],
      file_exists( $path_legacy ) ? filemtime( $path_legacy ) : null
    );
  }
  // それ以外（非WooCommerceページ）は読み込まない → パフォーマンス最適化
}
add_action( 'wp_enqueue_scripts', 'proshopwave_enqueue_woocommerce_styles', 20 );


// -----------------------------
// scriptタグに type="module" を追加
// -----------------------------
function add_type_attribute($tag, $handle, $src)
{
  $module_scripts = ['main-js'];
  if (in_array($handle, $module_scripts, true)) {
    return '<script type="module" src="' . esc_url($src) . '"></script>';
  }
  return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute', 10, 3);

// -----------------------------
// 2560px超え画像を縮小させない
// -----------------------------
add_filter('big_image_size_threshold', '__return_false');

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

// -----------------------------
// WPML 対応言語切り替え対応（必要に応じて）
// -----------------------------
// WPMLが有効な場合は、テンプレートで do_action('wpml_add_language_selector') などを使えます。

// -----------------------------
// WooCommerce サポートを有効化（ギャラリー機能も含む）
// -----------------------------
function proshopwave_add_woocommerce_support()
{
  // WooCommerce の基本機能（商品ページ、カートなど）をテーマに対応させる
  add_theme_support('woocommerce');
  // 商品画像ギャラリー：ズーム機能を有効化
  add_theme_support('wc-product-gallery-zoom');
  // 商品画像ギャラリー：ライトボックス（拡大表示）を有効化
  add_theme_support('wc-product-gallery-lightbox');
  // 商品画像ギャラリー：スライダー機能を有効化
  add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'proshopwave_add_woocommerce_support');

// -----------------------------
// WooCommerce 商品カテゴリの初期登録（パーツ・アパレル）
// -----------------------------
function proshopwave_register_product_categories()
{
  // パーツカテゴリ
  $parts_parent_slug = 'parts';
  $parts_parent_term = term_exists($parts_parent_slug, 'product_cat');

  if (!$parts_parent_term) {
    $parts_parent_term = wp_insert_term('パーツ', 'product_cat', [
      'slug' => $parts_parent_slug,
      'description' => '各種チューニング・補修用パーツのカテゴリ',
    ]);
  }

  $parts_child_categories = [
    ['name' => 'エンジン系',     'slug' => 'engine'],
    ['name' => '吸排気系',     'slug' => 'intake-exhaust'],
    ['name' => '冷却系',       'slug' => 'cooling'],
    ['name' => '駆動系',       'slug' => 'drivetrain'],
    ['name' => 'サスペンション', 'slug' => 'suspension'],
    ['name' => 'ブレーキ',     'slug' => 'brake'],
    ['name' => '電装系',       'slug' => 'electrical'],
    ['name' => '外装エアロ',   'slug' => 'exterior'],
    ['name' => '内装パーツ',   'slug' => 'interior'],
  ];

  foreach ($parts_child_categories as $child) {
    if (!term_exists($child['slug'], 'product_cat')) {
      wp_insert_term($child['name'], 'product_cat', [
        'slug' => $child['slug'],
        'parent' => is_array($parts_parent_term) ? $parts_parent_term['term_id'] : $parts_parent_term,
      ]);
    }
  }

  // アパレルカテゴリ
  $apparel_parent_slug = 'apparel';
  $apparel_parent_term = term_exists($apparel_parent_slug, 'product_cat');

  if (!$apparel_parent_term) {
    $apparel_parent_term = wp_insert_term('アパレル', 'product_cat', [
      'slug' => $apparel_parent_slug,
      'description' => 'チームグッズやウェアなどのアパレルカテゴリ',
    ]);
  }

  $apparel_child_categories = [
    ['name' => 'Tシャツ',    'slug' => 'tshirt'],
    ['name' => 'パーカー',   'slug' => 'hoodie'],
    ['name' => 'キャップ',   'slug' => 'cap'],
    ['name' => 'ステッカー', 'slug' => 'sticker'],
    ['name' => 'その他',     'slug' => 'other-apparel'],
  ];

  foreach ($apparel_child_categories as $child) {
    if (!term_exists($child['slug'], 'product_cat')) {
      wp_insert_term($child['name'], 'product_cat', [
        'slug' => $child['slug'],
        'parent' => is_array($apparel_parent_term) ? $apparel_parent_term['term_id'] : $apparel_parent_term,
      ]);
    }
  }
}
add_action('init', 'proshopwave_register_product_categories');


// -----------------------------
// WooCommerce 商品登録時にSKUを自動生成（登録日（yymmdd形式）＋投稿ID）
// -----------------------------
function proshopwave_generate_auto_sku($post_id)
{
  if (get_post_type($post_id) !== 'product') {
    return;
  }

  $sku = get_post_meta($post_id, '_sku', true);
  if (! empty($sku)) {
    return;
  }

  $date = date('ymd'); // 例：240701（2024年7月1日）
  $sku  = $date . '-' . $post_id;

  update_post_meta($post_id, '_sku', $sku);
}
add_action('save_post_product', 'proshopwave_generate_auto_sku');

// -----------------------------
// 単一商品ページに SKU（型番）を表示
// 位置: タイトル/価格のすぐ下（優先度 21）
// -----------------------------
add_action('woocommerce_single_product_summary', function () {
  global $product;
  if (! $product instanceof WC_Product) return;
  $sku = $product->get_sku();
  if (! $sku) return;
  echo '<p class="product-sku"><span class="product-sku__label">' . esc_html__('型番:', 'proshopwave') . '</span> <span class="product-sku__value">' . esc_html($sku) . '</span></p>';
}, 21);

// -----------------------------
// 単一商品ページに product_tag を表示（ピル型リンク）
// 位置: SKU/適合の近く（優先度 32）
// -----------------------------
add_action('woocommerce_single_product_summary', function () {
  global $product;
  if (! $product instanceof WC_Product) return;

  $terms = get_the_terms($product->get_id(), 'product_tag');
  if (empty($terms) || is_wp_error($terms)) return;

  echo '<div class="product-tags" aria-label="Product tags">';
  echo '<span class="product-tags__label">' . esc_html__('タグ:', 'proshopwave') . '</span>';
  echo '<ul class="product-tags__list">';
  foreach ($terms as $t) {
    $url = get_term_link($t);
    if (is_wp_error($url)) continue;
    printf(
      '<li class="product-tags__item"><a class="product-tags__link" href="%s" rel="tag">%s</a></li>',
      esc_url($url),
      esc_html($t->name)
    );
  }
  echo '</ul></div>';
}, 25);

// -----------------------------
// WooCommerceの商品メタ情報（SKU・カテゴリー・タグなど）を非表示にする
// -----------------------------
function proshopwave_remove_product_meta()
{
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
}
add_action('woocommerce_before_single_product', 'proshopwave_remove_product_meta');

// -----------------------------
// WooCommerce 商品ループ <li> に fadeup クラスを追加
// -----------------------------
function add_fadeup_class_to_product_loop_item($classes)
{
  $classes[] = 'fadeup';
  return $classes;
}
add_filter('woocommerce_post_class', 'add_fadeup_class_to_product_loop_item');

// -----------------------------
// WooCommerce 商品ループから「カートに追加」ボタンを削除（商品ページでは表示）
// -----------------------------
function remove_loop_add_to_cart_button()
{
  if (! is_product()) {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
  }
}
add_action('init', 'remove_loop_add_to_cart_button');

// -----------------------------
// WooCommerce 商品ループの自動リンク(<a> 開始/終了)を全体で無効化
// 目的: ループ内の空のアンカーを出力しないようにし、明示的に用意した .product-card__more のみで詳細ページへリンクさせるため。
// 影響範囲: 商品アーカイブ/一覧ループ全体。単一商品ページは影響なし。
// -----------------------------
add_action('init', function () {
  // `<a href="..." class="woocommerce-LoopProduct-link ...">` の開始タグを無効化
  remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);

  // 上記開始タグに対応する閉じタグを無効化
  remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
});

// -----------------------------
// WooCommerce サイドバーを全ページで無効化
// -----------------------------
add_action('init', function () {
  remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
});

// ---------------------------------------------
// WooCommerce: 標準パンくず出力を無効化（テーマ側で統一）
// ---------------------------------------------
add_action('init', function () {
  // WooCommerce が有効な場合のみ実行
  if (function_exists('remove_action')) {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
  }
}, 99);

// -----------------------------
// WooCommerceのパンくずリスト（breadcrumb）のマークアップをカスタマイズ
// -----------------------------
function custom_woocommerce_breadcrumbs($defaults)
{
  $defaults['delimiter']    = ''; // 区切り文字（>）はCSSや ::before で制御するため空に
  $defaults['wrap_before']  = '<div class="woocommerce-breadcrumb"><ul class="breadcrumb__list">';
  $defaults['wrap_after']   = '</ul></div>';
  $defaults['before']       = '<li class="breadcrumb__item">';
  $defaults['after']        = '</li>';

  return $defaults;
}
add_filter('woocommerce_breadcrumb_defaults', 'custom_woocommerce_breadcrumbs');

// -----------------------------
// WooCommerce アーカイブ説明から不要な div と p タグを除去
// -----------------------------
remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);

add_action('woocommerce_archive_description', function () {
  if (is_product_taxonomy() && 0 === absint(get_query_var('paged'))) {
    $description = term_description();
    if ($description) {
      // pタグやdivタグを除去してテキストのみ出力
      echo esc_html(wp_strip_all_tags($description));
    }
  }
}, 10);

// -----------------------------
// WooCommerce 商品一覧の表示件数を変更（例：12件）
// -----------------------------
add_filter('loop_shop_per_page', function ($cols) {
  return 12; // 表示件数を変更
}, 20);

// ==================================================
// WooCommerce: レビュー投稿の送信ボタンを <input> から <button> に変更
// 目的: @mixin button-cta の疑似要素（::before/::after）を使うため
// ==================================================
add_filter('woocommerce_product_review_comment_form_args', function ($args) {
  // 既存の class_submit を尊重しつつ、セクション共通のCTAクラスを付与
  $class_submit = isset($args['class_submit']) && $args['class_submit'] !== ''
    ? $args['class_submit'] . ' section__button'
    : 'section__button';

  // comment_form の submit_button フォーマットを <button> に置き換え
  // %1$s=name, %2$s=id, %3$s=class, %4$s=ラベル
  $args['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s">'
    . '<span class="section__button-inner">%4$s</span>'
    . '</button>';

  // 置き換え後に class を正しく渡す
  $args['class_submit'] = $class_submit;

  // ラッパーは既定と同じ（%1$s=submit_button, %2$s=hidden fields）
  $args['submit_field'] = '<p class="form-submit">%1$s %2$s</p>';

  // 念のためラベル未指定時の既定値
  if (empty($args['label_submit'])) {
    $args['label_submit'] = esc_html__('Submit', 'woocommerce');
  }

  return $args;
});

// ----------------------------------------------
// Helper: 二重URLエンコード等を考慮してクエリ文字列を安全に取得
// 例) "%25e3%2582%25a2..." → rawurldecode 2段階で「アパレル」へ
// ----------------------------------------------
function get_query_slug($key)
{
  if (! isset($_GET[$key])) {
    return '';
  }
  $raw = $_GET[$key];
  if (is_array($raw)) {
    return '';
  }
  // WordPressの「マジッククォート」互換の自動エスケープが残る可能性があるので、まずはアンスラッシュして素の文字に戻す
  $val = wp_unslash($raw);

  // 1回デコード（ UTF-8 の生文字（例：「アパレル」）に戻す）
  // ここで rawurldecode を使うのは、+ をスペースに変換しないため（urldecode は +→空白にする）。
  $decoded = rawurldecode($val);
  // まだ %XX パターンが残っている（=二重エンコードの可能性）ならもう一度
  // 「% に続く16進数2桁」＝パーセントエンコード（%HH）1バイト分を検出するための正規表現
  if (preg_match('/%[0-9a-fA-F]{2}/', $decoded)) {
    $decoded = rawurldecode($decoded);
  }

  // テキストとしてサニタイズして返す（日本語スラッグも許容）
  return sanitize_text_field($decoded);
}

// -----------------------------
// 商品一覧の絞り込みをメインクエリへ反映（カテゴリ / タグ / ブランド）
// 対象: ショップ一覧 / 商品カテゴリ・タグなどの商品系アーカイブ
// URL例: ?product_cat=slug&product_tag=slug&product_brand=slug または ?pa_brand=slug
// -----------------------------
add_action('pre_get_posts', function ($query) {
  // 管理画面やメインクエリ以外は除外
  if (is_admin() || ! $query->is_main_query()) {
    return;
  }

  // ショップ一覧 or WooCommerce の商品系タクソノミーのみ対象
  if (! (is_shop() || is_product_taxonomy())) {
    return;
  }

  // 既存 tax_query を取得して配列化
  $tax_query = (array) $query->get('tax_query');

  // --- 1) カテゴリ（product_cat）
  $cat = get_query_slug('product_cat');
  if ($cat !== '') {
    $tax_query[] = [
      'taxonomy'         => 'product_cat',
      'field'            => 'slug',
      'terms'            => [$cat],
      'operator'         => 'IN',
      'include_children' => true,
    ];
  }

  // --- 2) タグ（product_tag）
  $tag = get_query_slug('product_tag');
  if ($tag !== '') {
    $tax_query[] = [
      'taxonomy' => 'product_tag',
      'field'    => 'slug',
      'terms'    => [$tag],
      'operator' => 'IN',
    ];
  }

  // --- 3) ブランド（環境により taxonomy 名が異なる想定: product_brand or pa_brand）
  $brand_tax = taxonomy_exists('product_brand') ? 'product_brand' : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');
  if ($brand_tax) {
    $param_key = $brand_tax; // URLキーは taxonomy 名に合わせる方針
    $brand = get_query_slug($param_key);
    if ($brand !== '') {
      $tax_query[] = [
        'taxonomy' => $brand_tax,
        'field'    => 'slug',
        'terms'    => [$brand],
        'operator' => 'IN',
      ];
    }
  }

  if (! empty($tax_query)) {
    if (! isset($tax_query['relation'])) {
      $tax_query['relation'] = 'AND';
    }
    $query->set('tax_query', $tax_query);
  }
});

// -----------------------------
// 重複防止: before_shop_loop 標準の件数/並び替えは削除（自前のコントロール"product-archive__controls"を使用）
// -----------------------------
add_action('init', function () {
  remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
  remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});

// -----------------------------
// /shop/ を常に WooCommerce 側のアーカイブテンプレートで表示
// 目的: テーマ直下の archive-product.php が拾われるケースを避け、
//       woocommerce/archive-product.php（= フィルターバー出力版）を優先させる
// -----------------------------
add_filter('template_include', function ($template) {
  // is_shop() は WooCommerce 有効時のみ
  if (function_exists('is_shop') && is_shop()) {
    // Woo のテンプレートロケータで優先解決
    if (function_exists('wc_locate_template')) {
      $wc_template = wc_locate_template('archive-product.php');
      if (! empty($wc_template)) {
        return $wc_template;
      }
    }
    // フォールバック: 子テーマ/親テーマの woocommerce/archive-product.php
    $fallback_child = get_stylesheet_directory() . '/woocommerce/archive-product.php';
    if (file_exists($fallback_child)) {
      return $fallback_child;
    }
    $fallback_parent = get_template_directory() . '/woocommerce/archive-product.php';
    if (file_exists($fallback_parent)) {
      return $fallback_parent;
    }
  }
  return $template;
}, 50);

// -----------------------------
// 単一商品ページ：ブランド(product_brand/pa_brand)から「適合車種」を表示
// 表示位置: シングル商品サマリー（メタの代替）優先度25
// 表示ポリシー:
// - 型式（孫）が存在する場合 → ブランドとブランド›車種は表示せず、ブランド›車種›型式のみ表示
// - 型式が無く、車種まで存在する場合 → ブランド›車種を表示（ブランド単独は不要）
// - 車種が無い場合 → ブランドのみ表示
// -----------------------------
add_action('woocommerce_single_product_summary', function () {
  // 対応タクソノミー（product_brand優先、なければpa_brand）
  $tax = taxonomy_exists('product_brand') ? 'product_brand' : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');
  if (!$tax) {
    return;
  }

  $product_id = get_the_ID();
  if (!$product_id) {
    return;
  }
  $terms = wp_get_post_terms($product_id, $tax, ['hide_empty' => false]);

  if (is_wp_error($terms) || empty($terms)) {
    return;
  }

  // --- 適合表示の制御ロジック ---
  // 管理UIのチェックボックス（pw_show_fitment）で制御
  // 管理画面のチェックボックス（pw_show_fitment）が '0' のものは除外し、
  // 空文字（未設定）または '1' は表示扱い（デフォルト表示）にします。
  $terms_showable = array_filter($terms, function ($t) {
    $flag = get_term_meta((int)$t->term_id, 'pw_show_fitment', true);
    return ($flag === '' || $flag === '1');
  });

  // 2) もし「表示可」タームが1つも無ければ、適合セクション自体を出力しない
  if (empty($terms_showable)) {
    return;
  }

  // 以降の処理は「表示可」タームを対象に進める
  $terms = array_values($terms_showable);

  // 各タームごとに親をたどり、[ブランド, 車種, 型式] の配列に
  $chains = [];
  foreach ($terms as $t) {
    $chain = [$t];
    $p = $t;
    // 先祖を上へ辿る（最大3階層まで保護）
    while (!empty($p->parent)) {
      $p = get_term((int)$p->parent, $t->taxonomy);
      if (!$p || is_wp_error($p)) break;
      array_unshift($chain, $p);
      if (count($chain) > 5) break;
    }
    // 1階層目: ブランド, 2: 車種, 3: 型式
    $chains[] = $chain;
  }
  // 表示ポリシーに従い分類
  $has_model = false; // 型式あり
  $has_car = false;   // 車種まで
  $only_brand = [];   // ブランドのみ
  $brand_car = [];    // ブランド›車種
  $brand_car_model = []; // ブランド›車種›型式
  foreach ($chains as $chain) {
    $len = count($chain);
    if ($len >= 3) {
      $has_model = true;
      $brand_car_model[] = $chain;
    } elseif ($len == 2) {
      $has_car = true;
      $brand_car[] = $chain;
    } elseif ($len == 1) {
      $only_brand[] = $chain;
    }
  }
  $lines = [];
  if ($has_model) {
    // 型式がある場合は型式のみ表示（ブランドやブランド›車種は出さない）
    foreach ($brand_car_model as $chain) {
      $labels = array_map(function ($t) {
        return esc_html($t->name);
      }, $chain);
      $lines[] = implode(' › ', $labels);
    }
  } elseif ($has_car) {
    // 車種まであればブランド›車種のみ
    foreach ($brand_car as $chain) {
      $labels = array_map(function ($t) {
        return esc_html($t->name);
      }, $chain);
      $lines[] = implode(' › ', $labels);
    }
  } else {
    // ブランドのみ
    foreach ($only_brand as $chain) {
      $labels = array_map(function ($t) {
        return esc_html($t->name);
      }, $chain);
      $lines[] = implode(' › ', $labels);
    }
  }
  // 重複除去 & 並び替え
  $lines = array_values(array_unique($lines));
  natcasesort($lines);
  if (empty($lines)) {
    return;
  }
?>
  <div class="product-fitment">
    <p class="product-fitment__title"><?php echo esc_html__('適合車種', 'proshopwave'); ?></p>
    <ul class="product-fitment__list">
      <?php foreach ($lines as $line): ?>
        <li class="product-fitment__item"><?php echo esc_html($line); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php
}, 25);


// ==================================================
// 管理画面: ブランドタクソノミーに「適合車種を表示」チェックを追加
// 対応: product_brand / pa_brand（存在する方）
// メタキー: pw_show_fitment ('1' = 表示, '0' = 非表示) ※デフォルトは '1'
// ==================================================
add_action('init', function () {
  $taxes = [];
  if (taxonomy_exists('product_brand')) $taxes[] = 'product_brand';
  if (taxonomy_exists('pa_brand')) $taxes[] = 'pa_brand';
  if (empty($taxes)) return;

  // 追加フォーム（新規作成時）
  foreach ($taxes as $tx) {
    add_action("{$tx}_add_form_fields", function ($taxonomy) {
  ?>
      <div class="form-field term-group">
        <label for="pw_show_fitment">適合車種を表示</label>
        <input type="checkbox" id="pw_show_fitment" name="pw_show_fitment" value="1" checked />
        <p class="description">このブランドのタームを商品の「適合車種」セクションに含めます。汎用ブランド（UNIVERSAL 等）やアパレルなど、適合が関係ない場合はチェックを外してください。</p>
      </div>
    <?php
    });
  }

  // 編集フォーム（既存編集時）
  foreach ($taxes as $tx) {
    add_action("{$tx}_edit_form_fields", function ($term, $taxonomy) {
      $checked = get_term_meta($term->term_id, 'pw_show_fitment', true);
      $checked = ($checked === '' || $checked === '1') ? 'checked' : '';
    ?>
      <tr class="form-field term-group-wrap">
        <th scope="row"><label for="pw_show_fitment">適合車種を表示</label></th>
        <td>
          <input type="checkbox" id="pw_show_fitment" name="pw_show_fitment" value="1" <?php echo $checked; ?> />
          <p class="description">このブランドのタームを商品の「適合車種」セクションに含めます。汎用ブランド（UNIVERSAL 等）やアパレルなど、適合が関係ない場合はチェックを外してください。</p>
        </td>
      </tr>
<?php
    }, 10, 2);
  }

  // 保存処理（新規・編集）
  foreach ($taxes as $tx) {
    add_action("created_{$tx}", function ($term_id) {
      $val = isset($_POST['pw_show_fitment']) ? '1' : '0';
      update_term_meta((int)$term_id, 'pw_show_fitment', $val);
    });
    add_action("edited_{$tx}", function ($term_id) {
      $val = isset($_POST['pw_show_fitment']) ? '1' : '0';
      update_term_meta((int)$term_id, 'pw_show_fitment', $val);
    });
  }

  // 管理一覧のカラムに状態を表示（任意）
  foreach ($taxes as $tx) {
    add_filter("manage_edit-{$tx}_columns", function ($columns) {
      $columns['pw_show_fitment'] = '適合表示';
      return $columns;
    });
    add_filter("manage_{$tx}_custom_column", function ($out, $column, $term_id) {
      if ($column === 'pw_show_fitment') {
        $val = get_term_meta((int)$term_id, 'pw_show_fitment', true);
        $out = ($val === '' || $val === '1') ? '表示' : '非表示';
      }
      return $out;
    }, 10, 3);
  }
});


// ---------------------------------------------
// WooCommerce: ヘッダーのカート数量バッジをAJAXで更新
// （wc-ajax=add_to_cart 後のフラグメントで .header__cart-count を差し替え）
// ---------------------------------------------
if ( function_exists( 'add_filter' ) ) {
  add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    if ( function_exists( 'WC' ) && WC()->cart ) {
      $count = (int) WC()->cart->get_cart_contents_count();
      $class = $count > 0 ? ' is-active' : '';
    } else {
      $count = 0;
      $class = '';
    }

      // 出力バッファ開始（画面には、まだ header__cart-count は表示させない）
      ob_start();
    ?>
    <span class="header__cart-count<?php echo esc_attr( $class ); ?>" aria-live="polite" aria-atomic="true"><?php echo esc_html( $count ); ?></span>
    <?php
      // バッファの中身を取り出して変数に代入し、バッファをクリア
      $fragments['span.header__cart-count'] = ob_get_clean();

    return $fragments;
  } );
}
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
function get_author_social_url($user_id, $service) {
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
    'twitter' => 'https://x.com/%s',
    'instagram' => 'https://www.instagram.com/%s',
    'facebook'  => 'https://www.facebook.com/%s',
    'youtube'   => 'https://www.youtube.com/@%s',
  ];

  if (! isset($bases[$service])) return '';

  // 5) ハンドルをURLエンコードして整形
  return sprintf($bases[$service], rawurlencode($handle));
}
// ---------------------------------------------
// Contact Form 7: 自動 <p> / <br> 生成を無効化
// ---------------------------------------------
// フォームタグ内のテキストから自動で <p> や <br> を挿入する機能をOFFにし、
// テンプレート側で記述したBEM構造のマークアップをそのまま出力させる。
add_filter('wpcf7_autop_or_not', '__return_false');