<?php

/**
 * WooCommerce support & customizations
 *
 * WooCommerce 関連のサポート・拡張処理を集約しています。
 * - テーマサポート（ギャラリー機能など）
 * - 商品カテゴリの初期登録
 * - SKU 自動採番と表示
 * - 商品タグ表示
 * - 商品ループの見た目・挙動カスタマイズ
 * - パンくずリスト・絞り込み・テンプレート制御
 * - 適合車種（ブランド階層）表示
 * - カートバッジの AJAX 更新
 *
 * @package PRO_SHOP_WAVE
 */

// -----------------------------
// WooCommerce サポートを有効化（ギャラリー機能も含む）
// -----------------------------
function proshopwave_add_woocommerce_support()
{
  // WooCommerce の基本機能（商品ページ、カートなど）をテーマに対応させる
  add_theme_support('woocommerce');
  // 商品画像ギャラリー：ズーム機能を有効化（現在は無効化中）
  // add_theme_support('wc-product-gallery-zoom');
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

  if (! $parts_parent_term) {
    $parts_parent_term = wp_insert_term('パーツ', 'product_cat', [
      'slug'        => $parts_parent_slug,
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
    if (! term_exists($child['slug'], 'product_cat')) {
      wp_insert_term($child['name'], 'product_cat', [
        'slug'   => $child['slug'],
        'parent' => is_array($parts_parent_term) ? $parts_parent_term['term_id'] : $parts_parent_term,
      ]);
    }
  }

  // アパレルカテゴリ
  $apparel_parent_slug = 'apparel';
  $apparel_parent_term = term_exists($apparel_parent_slug, 'product_cat');

  if (! $apparel_parent_term) {
    $apparel_parent_term = wp_insert_term('アパレル', 'product_cat', [
      'slug'        => $apparel_parent_slug,
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
    if (! term_exists($child['slug'], 'product_cat')) {
      wp_insert_term($child['name'], 'product_cat', [
        'slug'   => $child['slug'],
        'parent' => is_array($apparel_parent_term) ? $apparel_parent_term['term_id'] : $apparel_parent_term,
      ]);
    }
  }
}
add_action('init', 'proshopwave_register_product_categories');


// -----------------------------
// WooCommerce 商品登録時にSKUを自動生成（WAVE-000123形式 / 投稿IDベース）
// -----------------------------
function proshopwave_generate_auto_sku($post_id)
{
  // 対象: 商品投稿タイプのみ
  if (get_post_type($post_id) !== 'product') {
    return;
  }

  // すでにSKUが手動入力されている場合は上書きしない
  $sku = get_post_meta($post_id, '_sku', true);
  if (! empty($sku)) {
    return;
  }

  // 投稿IDを6桁ゼロ埋めして「WAVE-000123」のような形式でSKUを自動生成
  $sku = sprintf('WAVE-%06d', (int) $post_id);

  update_post_meta($post_id, '_sku', $sku);
}
add_action('save_post_product', 'proshopwave_generate_auto_sku');

// -----------------------------
// 単一商品ページに SKU（型番）を表示
// 位置: タイトル/価格のすぐ下（優先度 21）
// -----------------------------
add_action('woocommerce_single_product_summary', function () {
  global $product;
  if (! $product instanceof WC_Product) {
    return;
  }
  $sku = $product->get_sku();
  if (! $sku) {
    return;
  }
  echo '<p class="product-sku"><span class="product-sku__label">' . esc_html__('型番:', 'proshopwave') . '</span> <span class="product-sku__value">' . esc_html($sku) . '</span></p>';
}, 21);

// -----------------------------
// 単一商品ページに product_tag を表示（ピル型リンク）
// 位置: SKU/適合の近く（優先度 25）
// -----------------------------
add_action('woocommerce_single_product_summary', function () {
  global $product;
  if (! $product instanceof WC_Product) {
    return;
  }

  $terms = get_the_terms($product->get_id(), 'product_tag');
  if (empty($terms) || is_wp_error($terms)) {
    return;
  }

  echo '<div class="product-tags" aria-label="Product tags">';
  echo '<span class="product-tags__label">' . esc_html__('タグ:', 'proshopwave') . '</span>';
  echo '<ul class="product-tags__list">';
  foreach ($terms as $t) {
    $url = get_term_link($t);
    if (is_wp_error($url)) {
      continue;
    }
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
// 目的: ループ内の空のアンカーを出力しないようにし、
//       明示的に用意した .product-card__more のみで詳細ページへリンクさせるため。
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
  $defaults['delimiter']   = ''; // 区切り文字（>）はCSSや ::before で制御するため空に
  $defaults['wrap_before'] = '<div class="woocommerce-breadcrumb"><ul class="breadcrumb__list">';
  $defaults['wrap_after']  = '</ul></div>';
  $defaults['before']      = '<li class="breadcrumb__item">';
  $defaults['after']       = '</li>';

  return $defaults;
}
add_filter('woocommerce_breadcrumb_defaults', 'custom_woocommerce_breadcrumbs');

// ---------------------------------------------
// Helper: 二重URLエンコード等を考慮してクエリ文字列を安全に取得
// 例) "%25e3%2582%25a2..." → rawurldecode 2段階で「アパレル」へ
// ---------------------------------------------
function get_query_slug($key)
{
  if (! isset($_GET[$key])) {
    return '';
  }
  $raw = $_GET[$key];
  if (is_array($raw)) {
    return '';
  }
  // WordPressの「マジッククォート」互換の自動エスケープが残る可能性があるので、
  // まずはアンスラッシュして素の文字に戻す
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
// WooCommerce: クエリ型ブランド絞り込み時のパンくずを「ブランド > 階層 > 用途」に合わせる
// 例: /shop/?product_brand=mow → ホーム > ブランド > MOW
//     /shop/?product_brand=s14 → ホーム > ブランド > NISSAN > SILVIA > S14
// -----------------------------
// 補足:
// product_brand は WooCommerce 標準の product_cat と異なり、
// /shop/?product_brand=... のようなクエリだけでは「タクソノミーアーカイブ」と判定されません。
// WooCommerce 側では通常のショップアーカイブ（/shop/）に対する絞り込みとみなされるため、
// そのままだとパンくずが「ホーム > SHOP」のままになります。
// ここではクエリからブランドタームを読み取り、実際のブランド階層
// （例: NISSAN > SILVIA > S14）に合わせてパンくず配列を差し替えています。
add_filter('woocommerce_get_breadcrumb', function ($crumbs, $breadcrumb) {
  // WooCommerceが有効で、かつメインのショップページのときだけ処理
  if (! function_exists('is_shop') || ! is_shop()) {
    return $crumbs;
  }

  // 対応しているブランド用タクソノミーを判定（product_brand 優先）
  $brand_tax = taxonomy_exists('product_brand')
    ? 'product_brand'
    : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');

  if (! $brand_tax) {
    return $crumbs;
  }

  // クエリパラメータからブランドスラッグを取得
  $brand_slug = get_query_slug($brand_tax);
  if ($brand_slug === '') {
    // ?product_brand=（または対応タクソノミー）が付いていない通常の /shop/ はそのまま
    return $crumbs;
  }

  // スラッグからブランドタームを取得
  $term = get_term_by('slug', $brand_slug, $brand_tax);
  if (! $term || is_wp_error($term)) {
    return $crumbs;
  }

  // 既存の「ホーム」パンくずだけ再利用（多言語環境を尊重）
  $home_label = isset($crumbs[0][0]) ? $crumbs[0][0] : esc_html__('HOME', 'woocommerce');
  $home_link  = isset($crumbs[0][1]) ? $crumbs[0][1] : home_url('/');

  // 「ブランド」ラベル（必要なら .po/.mo 側で翻訳）
  $brand_root_label = __('ブランド', 'proshopwave');
  // ブランド一覧のリンクが無い場合は空文字のままでもOK
  $brand_root_link  = ''; // 例: 専用のブランド一覧ページを作ったらそのURLに差し替え

  // --- 親ブランド階層（NISSAN > SILVIA > S14 など）をたどる ---
  $chain_terms = [];

  // 先祖タームIDの配列（親 → 祖父…）
  $ancestors = get_ancestors($term->term_id, $brand_tax, 'taxonomy');
  if (! empty($ancestors)) {
    // 上位階層から順に並べたいので、ID配列を逆順に
    $ancestors = array_reverse($ancestors);
    foreach ($ancestors as $ancestor_id) {
      $ancestor_term = get_term($ancestor_id, $brand_tax);
      if ($ancestor_term && ! is_wp_error($ancestor_term)) {
        $chain_terms[] = $ancestor_term;
      }
    }
  }

  // 最後に現在のターム（例: S14）を追加
  $chain_terms[] = $term;

  // 「ホーム > ブランド > NISSAN > SILVIA > S14」という配列を組み立て直す
  $new_crumbs = [
    [$home_label, $home_link],
    [$brand_root_label, $brand_root_link],
  ];

  foreach ($chain_terms as $t) {
    $link = get_term_link($t);
    if (is_wp_error($link)) {
      $new_crumbs[] = [$t->name, ''];
    } else {
      $new_crumbs[] = [$t->name, $link];
    }
  }

  return $new_crumbs;
}, 10, 2);

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
    $brand     = get_query_slug($param_key);
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
// 重複防止: before_shop_loop 標準の件数/並び替えは削除
// （自前のコントロール "product-archive__controls" を使用）
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
  if (! $tax) {
    return;
  }

  $product_id = get_the_ID();
  if (! $product_id) {
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
    $flag = get_term_meta((int) $t->term_id, 'pw_show_fitment', true);
    return ($flag === '' || $flag === '1');
  });

  // 2) もし「表示可」タームが1つも無ければ、適合セクション自体を出力しない
  if (empty($terms_showable)) {
    return;
  }

  // 以降の処理は「表示可」タームを対象に進める
  $terms  = array_values($terms_showable);
  $chains = [];

  // 各タームごとに親をたどり、[ブランド, 車種, 型式] の配列に
  foreach ($terms as $t) {
    $chain = [$t];
    $p     = $t;
    // 先祖を上へ辿る（最大5階層まで保護）
    while (! empty($p->parent)) {
      $p = get_term((int) $p->parent, $t->taxonomy);
      if (! $p || is_wp_error($p)) {
        break;
      }
      array_unshift($chain, $p);
      if (count($chain) > 5) {
        break;
      }
    }
    $chains[] = $chain;
  }

  // 表示ポリシーに従い分類
  $has_model        = false; // 型式あり
  $has_car          = false; // 車種まで
  $only_brand       = [];    // ブランドのみ
  $brand_car        = [];    // ブランド›車種
  $brand_car_model  = [];    // ブランド›車種›型式

  foreach ($chains as $chain) {
    $len = count($chain);
    if ($len >= 3) {
      $has_model         = true;
      $brand_car_model[] = $chain;
    } elseif ($len === 2) {
      $has_car    = true;
      $brand_car[] = $chain;
    } elseif ($len === 1) {
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
  if (taxonomy_exists('product_brand')) {
    $taxes[] = 'product_brand';
  }
  if (taxonomy_exists('pa_brand')) {
    $taxes[] = 'pa_brand';
  }
  if (empty($taxes)) {
    return;
  }

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
      update_term_meta((int) $term_id, 'pw_show_fitment', $val);
    });
    add_action("edited_{$tx}", function ($term_id) {
      $val = isset($_POST['pw_show_fitment']) ? '1' : '0';
      update_term_meta((int) $term_id, 'pw_show_fitment', $val);
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
        $val = get_term_meta((int) $term_id, 'pw_show_fitment', true);
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
if (function_exists('add_filter')) {
  add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    if (function_exists('WC') && WC()->cart) {
      $count = (int) WC()->cart->get_cart_contents_count();
      $class = $count > 0 ? ' is-active' : '';
    } else {
      $count = 0;
      $class = '';
    }

    // 出力バッファ開始（画面には、まだ header__cart-count は表示させない）
    ob_start();
    ?>
    <span class="header__cart-count<?php echo esc_attr($class); ?>" aria-live="polite" aria-atomic="true"><?php echo esc_html($count); ?></span>
<?php
    // バッファの中身を取り出して変数に代入し、バッファをクリア
    $fragments['span.header__cart-count'] = ob_get_clean();

    return $fragments;
  });
}
