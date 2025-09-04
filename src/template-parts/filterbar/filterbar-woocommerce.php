<?php

/**
 * Template part for Filterbar (WooCommerce)
 *  - PC: ドロップダウン
 *  - SP: モーダル
 */

if (! defined('ABSPATH')) {
  exit;
}

// --------------------------------------------------
// Helper: 安全にターム配列を取得
// --------------------------------------------------
function get_terms_safe($args)
{
  $terms = get_terms($args);
  return (! is_wp_error($terms) && ! empty($terms) && is_array($terms)) ? $terms : [];
}

// --------------------------------------------------
// 1) カテゴリ：親=parts/apparel を優先して取得、無ければ親カテゴリ一覧を列挙
// --------------------------------------------------
$parent_slugs = ['parts', 'apparel'];
// 親カテゴリ（parts/apparelを優先して取得）
$parent_terms = get_terms_safe([
  'taxonomy'   => 'product_cat',
  'hide_empty' => true,
  'slug'       => $parent_slugs,
]);

if (count($parent_terms) < 2) {
  // 無ければ「親=0」（トップレベルカテゴリ一覧）を列挙してフォールバック
  $parent_terms = get_terms_safe([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
}

// 親ごとの子カテゴリマップを構築
$children_map = [];
foreach ($parent_terms as $p) {
  // --------------------------------------------------
  // $children_map =「キー＝親カテゴリの slug」→「値＝子カテゴリ（WP_Termオブジェクトの配列）」の連想配列を作る
  // 
  // $children_map = [
  //   'parts'   => [ WP_Term(/*外装エアロ*/),
  //                  WP_Term(/*冷却系*/),
  //                  ... ],
  //   'apparel' => [ WP_Term(/*Tシャツ*/),
  //                  WP_Term(/*パーカー*/),
  //                  ... ],
  //    ];
  // 
  // 処理の順序
  // 1.	右辺を評価（get_terms_safe([...]) を実行して、その戻り値を得る）
  // 2.	その結果を 左辺のキー $p->slug に代入（= 配列にキーが新規作成される or 既存キーが上書きされる）
  // 
  // 右辺の評価が完了するまでは、そのキーはまだ作られず、右辺が返った瞬間にキーが生成される
  // --------------------------------------------------

  $children_map[$p->slug] = get_terms_safe([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    // 親タームID直下の“子”だけを取得（孫以降は含まれない）、(int) キャストは安全のため型を明確化
    'parent'     => (int) $p->term_id,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
}

// --------------------------------------------------
// 2) タグ：人気順トップ10
// --------------------------------------------------
$popular_tags = get_terms_safe([
  'taxonomy'   => 'product_tag',
  'hide_empty' => true,
  'orderby'    => 'count',
  'order'      => 'DESC',
  'number'     => 10,
]);

// --------------------------------------------------
// 3) ブランド： product_brand があれば使用、無ければ pa_brand を試す
// --------------------------------------------------
$brand_tax = taxonomy_exists('product_brand') ? 'product_brand' : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');

// ブランド階層データ
$brand_parents      = []; // 親：メーカー
$brand_models_map   = []; // 子：車種（キー=親slug）
$brand_chassis_map  = []; // 孫：型式（キー=子slug）

if ($brand_tax) {
  // 親（メーカー）
  $brand_parents = get_terms_safe([
    'taxonomy'   => $brand_tax,
    'hide_empty' => true,
    'parent'     => 0,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);

  // 親→子（車種）
  foreach ($brand_parents as $bp) {
    $brand_models_map[$bp->slug] = get_terms_safe([
      'taxonomy'   => $brand_tax,
      'hide_empty' => true,
      'parent'     => (int)$bp->term_id,
      'orderby'    => 'name',
      'order'      => 'ASC',
    ]);

    // 子→孫（型式）
    foreach ($brand_models_map[$bp->slug] as $mdl) {
      $brand_chassis_map[$mdl->slug] = get_terms_safe([
        'taxonomy'   => $brand_tax,
        'hide_empty' => true,
        'parent'     => (int)$mdl->term_id,
        'orderby'    => 'name',
        'order'      => 'ASC',
      ]);
    }
  }
}

// 補助：表示テキスト
function term_label($t)
{
  return esc_html($t->name);
}
function term_value($t)
{
  return esc_attr($t->slug);
}
?>

<!-- フィルターバー（ピル） -->
<div class="filterbar">
  <button class="filterbar__pill js-open-dropdown" data-target="cat-dropdown" aria-expanded="false">
    <span class="filterbar__pill-label">カテゴリー</span>
    <span class="filterbar__pill-value" id="pill-cat">All</span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="tag-dropdown" aria-expanded="false">
    <span class="filterbar__pill-label">タグ</span>
    <span class="filterbar__pill-value" id="pill-tag">All</span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="brand-dropdown" aria-expanded="false">
    <span class="filterbar__pill-label">ブランド</span>
    <span class="filterbar__pill-value" id="pill-brand">All</span>
  </button>
  <button class="filterbar__open sp-only-block js-open-modal" aria-label="フィルターを開く">
    条件で絞り込む
  </button>
</div>

<!-- PC: ドロップダウン パネル群 -->
<div class="pc-only-block">
  <!-- カテゴリ -->
  <div class="filterbar__dropdown" id="cat-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="カテゴリで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>カテゴリを選択</h3>
          <div class="filterbar__options" id="pc-cat-parent">
            <?php foreach ($parent_terms as $pt) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($pt); ?>"><?php echo term_label($pt); ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <?php foreach ($parent_terms as $pt) : ?>
          <div class="filterbar__group">
            <h3><?php echo term_label($pt); ?></h3>
            <div class="filterbar__options" id="pc-cat-children-<?php echo term_value($pt); ?>">
              <?php foreach ($children_map[$pt->slug] as $ct) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($ct); ?>"><?php echo term_label($ct); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="filterbar__actions">
        <button class="filterbar__btn js-reset">カテゴリーをクリア</button>
        <button class="filterbar__btn filterbar__btn--primary js-apply">適用</button>
      </div>
    </div>
  </div>

  <!-- タグ -->
  <div class="filterbar__dropdown" id="tag-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="タグで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>人気タグ</h3>
          <div class="filterbar__options" id="pc-tag-popular">
            <?php foreach ($popular_tags as $tg) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($tg); ?>" data-label="<?php echo term_label($tg); ?>"><?php echo term_label($tg); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filterbar__group">
          <h3>タグを検索</h3>
          <input type="text" id="pc-tag-search" placeholder="タグ名で検索（例: エアロ）" />
          <div class="filterbar__options" id="pc-tag-results"></div>
        </div>
        <div class="filterbar__group">
          <h3>選択中</h3>
          <div class="filterbar__options" id="pc-tag-selected"></div>
        </div>
      </div>
      <div class="filterbar__actions">
        <button class="filterbar__btn js-reset-tag">タグをクリア</button>
        <button class="filterbar__btn filterbar__btn--primary js-apply">適用</button>
      </div>
    </div>
  </div>

  <!-- ブランド -->
  <div class="filterbar__dropdown" id="brand-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="ブランドで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>ブランド/メーカー</h3>
          <div class="filterbar__options" id="pc-brand-maker">
            <?php if (!empty($brand_parents)) : ?>
              <?php foreach ($brand_parents as $bp) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>"><?php echo term_label($bp); ?></button>
              <?php endforeach; ?>
            <?php else : ?>
              <span class="muted">ブランド用タクソノミーが未登録です（product_brand / pa_brand を想定）。</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="filterbar__group">
          <h3>車種</h3>
          <?php foreach ($brand_parents as $bp) : ?>
            <div class="filterbar__options" id="pc-brand-model-<?php echo term_value($bp); ?>" data-parent="<?php echo term_value($bp); ?>">
              <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($mdl); ?>" data-parent="<?php echo term_value($bp); ?>"><?php echo term_label($mdl); ?></button>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="filterbar__group">
          <h3>型式</h3>
          <?php foreach ($brand_parents as $bp) : ?>
            <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
              <div class="filterbar__options" id="pc-brand-chassis-<?php echo term_value($mdl); ?>" data-model="<?php echo term_value($mdl); ?>">
                <?php foreach (($brand_chassis_map[$mdl->slug] ?? []) as $chs) : ?>
                  <button class="filterbar__chip" data-value="<?php echo term_value($chs); ?>" data-parent="<?php echo term_value($mdl); ?>"><?php echo term_label($chs); ?></button>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="filterbar__actions">
        <button class="filterbar__btn js-reset-brand">ブランドをクリア</button>
        <button class="filterbar__btn filterbar__btn--primary js-apply">適用</button>
      </div>
    </div>
  </div>
</div>

<!-- SP: モーダル -->
<div class="sp-only-block">
  <div class="filterbar__modal" id="filterbar-modal" aria-hidden="true">
    <div class="filterbar__modal-scrim js-close-modal" tabindex="-1"></div>
    <div class="filterbar__modal-panel" role="dialog" aria-label="フィルター">
      <div class="filterbar__modal-header">
        <strong>絞り込み</strong>
        <button class="filterbar__btn js-close-modal">閉じる</button>
      </div>

      <details class="filterbar__accordion" open>
        <summary>カテゴリーで選ぶ</summary>
        <div class="filterbar__accordion-panel">
          <div class="filterbar__options" id="sp-cat-parent">
            <?php foreach ($parent_terms as $pt) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($pt); ?>"><?php echo term_label($pt); ?></button>
            <?php endforeach; ?>
          </div>
          <div class="filterbar__options" id="sp-cat-children">
            <!-- JSで parent 選択に応じて children を描画 -->
            <?php // 初期は空。JSで children_map を使い埋める想定 
            ?>
          </div>
        </div>
      </details>

      <details class="filterbar__accordion">
        <summary>タグで選ぶ</summary>
        <div class="filterbar__accordion-panel">
          <div class="filterbar__options" id="sp-tag-popular">
            <?php foreach ($popular_tags as $tg) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($tg); ?>" data-label="<?php echo term_label($tg); ?>"><?php echo term_label($tg); ?></button>
            <?php endforeach; ?>
          </div>
          <input type="text" id="sp-tag-search" placeholder="タグ名で検索（例: エアロ）" />
          <div class="filterbar__options" id="sp-tag-selected"></div>
        </div>
      </details>

      <?php if ($brand_tax) : ?>
        <details class="filterbar__accordion">
          <summary>ブランドで選ぶ</summary>
          <div class="filterbar__accordion-panel">
            <h4>ブランド/メーカー</h4>
            <div class="filterbar__options" id="sp-brand-maker">
              <?php foreach ($brand_parents as $bp) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>"><?php echo term_label($bp); ?></button>
              <?php endforeach; ?>
            </div>

            <h4>車種</h4>
            <?php foreach ($brand_parents as $bp) : ?>
              <div class="filterbar__options" id="sp-brand-model-<?php echo term_value($bp); ?>" data-parent="<?php echo term_value($bp); ?>">
                <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                  <button class="filterbar__chip" data-value="<?php echo term_value($mdl); ?>" data-parent="<?php echo term_value($bp); ?>"><?php echo term_label($mdl); ?></button>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>

            <h4>型式</h4>
            <?php foreach ($brand_parents as $bp) : ?>
              <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                <div class="filterbar__options" id="sp-brand-chassis-<?php echo term_value($mdl); ?>" data-model="<?php echo term_value($mdl); ?>">
                  <?php foreach (($brand_chassis_map[$mdl->slug] ?? []) as $chs) : ?>
                    <button class="filterbar__chip" data-value="<?php echo term_value($chs); ?>" data-parent="<?php echo term_value($mdl); ?>"><?php echo term_label($chs); ?></button>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endif; ?>

      <div class="filterbar__modal-footer">
        <button class="filterbar__btn" id="sp-reset">リセット</button>
        <button class="filterbar__btn filterbar__btn--primary" id="sp-apply">適用</button>
      </div>
    </div>
  </div>

</div>

<?php
// --------------------------------------------------
// PHP で用意したカテゴリ/ブランドのデータ（children_map）を JS へ渡す
// ますは、カテゴリ/ブランドのデータを、HTMLの data-* 属性にJSONとして埋め込み、
// それをフロントJSが JSON.parse で読み込む
// --------------------------------------------------

// 子カテゴリ用 $children_payload
$children_payload = [];
foreach ($parent_terms as $pt) {
  // array_map()でWP_Term オブジェクトの配列をJSが扱いやすい最小構造の連想配列に変換（{value: スラッグ, label: 表示名}）
  // array_map()は、array の各要素に callback を適用した後、 適用後の要素を含む array を返す
  $children_payload[$pt->slug] = array_map(function ($ct) {
    return ['value' => $ct->slug, 'label' => $ct->name];
  }, $children_map[$pt->slug]);
}

// ブランド3階層用 $brand_payload
$brand_payload = ['parents' => [], 'models' => [], 'chassis' => []];
foreach ($brand_parents as $bp) {
  // 親 parents（メーカー）配列に追加
  // ループ内で parents 配列の 末尾に新しい要素を 0,1,2,… の添字で追加
  // array_push($brand_payload['parents'], [...]) と同義
  $brand_payload['parents'][] = ['value' => $bp->slug, 'label' => $bp->name];

  // 親→子 models（車種）を格納（キー：親slug）
  // 1キー（親メーカー slug ）につき1配列をセットするのでforeachで回す必要なし
  $mdlList = $brand_models_map[$bp->slug] ?? [];
  // WP_Term オブジェクトの配列を、JSで扱いやすい形式の配列に変換
  $brand_payload['models'][$bp->slug] = array_map(function ($m) {
    return ['value' => $m->slug, 'label' => $m->name];
  }, $mdlList);

  // 子→孫 chassis（型式）を格納（キー：子slug）
  // 各車種（子）ごとに1配列を作って複数キー（車種 slug）へ格納する必要があるため、
  // 車種リスト $mdlList を foreach で1件ずつ回す必要あり
  foreach ($mdlList as $m) {
    $chsList = $brand_chassis_map[$m->slug] ?? [];
    // WP_Term オブジェクトの配列を、JSで扱いやすい形式の配列に変換
    $brand_payload['chassis'][$m->slug] = array_map(function ($c) {
      return ['value' => $c->slug, 'label' => $c->name];
    }, $chsList);
  }
}
?>
<?php
// --------------------------------------------------
// 現在アーカイブ（パス型 URL）でも JS 側へ初期選択を渡すための現在値
//  - カテゴリー: /product-category/... の最深タームの slug
//  - タグ: /product-tag/... の最深タームの slug
//  - ブランド: /brand/... or /pa_brand/... の最深タームの slug
// --------------------------------------------------
$current_cat_slug   = '';
$current_tag_slug   = '';
$current_brand_slug = '';

// カテゴリー（product_cat）
if (is_tax('product_cat')) {
  $qo = get_queried_object();
  if ($qo && ! is_wp_error($qo) && ! empty($qo->slug)) {
    $current_cat_slug = (string) $qo->slug;
  }
}

// タグ（product_tag）
if (is_tax('product_tag')) {
  $qo = get_queried_object();
  if ($qo && ! is_wp_error($qo) && ! empty($qo->slug)) {
    $current_tag_slug = (string) $qo->slug;
  }
}

// ブランド（product_brand / pa_brand）— 有効なタクソノミーのみ判定
if ($brand_tax && is_tax($brand_tax)) {
  $qo = get_queried_object();
  if ($qo && ! is_wp_error($qo) && ! empty($qo->slug)) {
    $current_brand_slug = (string) $qo->slug;
  }
}
?>
<?php $shop_url = wc_get_page_permalink('shop'); ?>
<div id="filterbar-dataset"
  data-shop-url="<?php echo esc_url($shop_url); ?>"
  data-current-cat="<?php echo esc_attr($current_cat_slug); ?>"
  data-current-tag="<?php echo esc_attr($current_tag_slug); ?>"
  data-current-brand="<?php echo esc_attr($current_brand_slug); ?>"
  data-children='
  <?php
  // wp_json_encode()で子カテゴリのデータをPHP配列（$children_payload）→ JSON文字列へ変換。
  // JSON_UNESCAPED_UNICODE → 日本語などを \u30a2... にエスケープせずそのまま出力
  // JSON_UNESCAPED_SLASHES → \/ のようにスラッシュをエスケープしない（URLなどが見やすい）
  echo wp_json_encode($children_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
  data-brand-tax="<?php echo esc_attr($brand_tax); ?>"
  data-brand='
  <?php
  // wp_json_encode()でブランドの3階層データをPHP配列（$children_payload）→ JSON文字列へ変換。
  echo wp_json_encode($brand_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
  style="display:none">
</div>