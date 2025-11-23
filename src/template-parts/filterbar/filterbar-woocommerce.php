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

// --------------------------------------------------
// 親カテゴリの表示順を固定（parts → apparel）
// get_terms() の結果順に依存せず、常に希望順で並べる
// --------------------------------------------------
$desired_order = ['parts', 'apparel'];
// $desired_order = ['parts', 'apparel'] を
// array_flip() で ['parts' => 0, 'apparel' => 1] に変換。
// これにより、slug をキーにして「希望の順番インデックス」を素早く参照できる。
$order_index = array_flip($desired_order);

// usort() で親カテゴリ配列を希望順（$desired_order）で並べ替える
// - usort: 配列を「比較関数」に基づいて並べ替えるPHPの関数
// - 比較関数は2つの要素($a, $b)を受け取り、順序を決める値を返す
usort($parent_terms, function ($a, $b) use ($order_index) {
  // $ai, $bi: 各タームのスラッグが$desired_order内で何番目か（インデックス）。未定義(slugが$desired_orderに無い)はPHP_INT_MAX（最大値）で一番後ろに。
  $ai = $order_index[$a->slug] ?? PHP_INT_MAX;
  $bi = $order_index[$b->slug] ?? PHP_INT_MAX;
  // インデックスが小さい方を先に（希望順）。未定義は後ろへ。
  // 比較結果が0なら順序は変えない（返り値0:等しい）、$ai<$biなら-1（$aが先）、$ai>$biなら1（$bが先）
  if ($ai === $bi) {
    // 同じインデックス（またはどちらも未定義）の場合は順序維持
    return 0;
  }
  // $aiが小さいほど前に
  return ($ai < $bi) ? -1 : 1;
});

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
// 2.5) タグ：全タグを検索母集団として取得（hide_empty=true, number=0 で全件）
// --------------------------------------------------
$all_tags = get_terms_safe([
  'taxonomy'   => 'product_tag',
  'hide_empty' => true,
  'orderby'    => 'name',
  'order'      => 'ASC',
  'number'     => 0, // 0 = 全件
]);

// JS で使いやすいように { slug: label, ... } の連想配列に圧縮
$tag_payload = [];
foreach ($all_tags as $tg) {
  $tag_payload[$tg->slug] = $tg->name; // スラッグ→人間可読ラベル
}

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
<div class="filterbar fadeup">
  <button class="filterbar__pill js-open-dropdown" data-target="cat-dropdown" aria-expanded="false">
    <i class="fa-solid fa-filter filterbar__pill-icon" aria-hidden="true"></i>
    <span class="filterbar__pill-label"><?php echo esc_html__('カテゴリー', 'proshopwave'); ?></span>
    <span class="filterbar__pill-value" id="pill-cat"><?php echo esc_html__('All', 'proshopwave'); ?></span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="tag-dropdown" aria-expanded="false">
    <i class="fa-solid fa-filter filterbar__pill-icon" aria-hidden="true"></i>
    <span class="filterbar__pill-label"><?php echo esc_html__('タグ', 'proshopwave'); ?></span>
    <span class="filterbar__pill-value" id="pill-tag"><?php echo esc_html__('All', 'proshopwave'); ?></span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="brand-dropdown" aria-expanded="false">
    <i class="fa-solid fa-filter filterbar__pill-icon" aria-hidden="true"></i>
    <span class="filterbar__pill-label"><?php echo esc_html__('ブランド', 'proshopwave'); ?></span>
    <span class="filterbar__pill-value" id="pill-brand"><?php echo esc_html__('All', 'proshopwave'); ?></span>
  </button>
  <button class="filterbar__open sp-only-block js-open-modal" aria-expanded="false">
    <?php echo esc_html__('絞り込み', 'proshopwave'); ?>
  </button>
</div>

<!-- PC: ドロップダウン パネル群 -->
<div class="pc-only-block">
  <!-- カテゴリ -->
  <div class="filterbar__dropdown" id="cat-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="<?php echo esc_attr__('カテゴリで選ぶ', 'proshopwave'); ?>">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3><?php echo esc_html__('カテゴリを選択', 'proshopwave'); ?></h3>
          <div class="filterbar__options" id="pc-cat-parent">
            <?php foreach ($parent_terms as $pt) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($pt); ?>"><?php echo term_label($pt); ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <?php foreach ($parent_terms as $pt) : ?>
          <div class="filterbar__group">
            <h3><?php echo esc_html(term_label($pt)); ?></h3>
            <div class="filterbar__options" id="pc-cat-children-<?php echo term_value($pt); ?>">
              <?php foreach ($children_map[$pt->slug] as $ct) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($ct); ?>"><?php echo term_label($ct); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="filterbar__actions">
        <button class="filterbar__btn js-reset"><?php echo esc_html__('カテゴリーをクリア', 'proshopwave'); ?></button>
        <button class="filterbar__btn filterbar__btn--primary js-apply"><?php echo esc_html__('適用', 'proshopwave'); ?></button>
      </div>
    </div>
  </div>

  <!-- タグ -->
  <div class="filterbar__dropdown" id="tag-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="<?php echo esc_attr__('タグで選ぶ', 'proshopwave'); ?>">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3><?php echo esc_html__('人気タグ', 'proshopwave'); ?></h3>
          <div class="filterbar__options" id="pc-tag-popular">
            <?php foreach ($popular_tags as $tg) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($tg); ?>" data-label="<?php echo term_label($tg); ?>"><?php echo term_label($tg); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filterbar__group">
          <h3><?php echo esc_html__('タグを検索', 'proshopwave'); ?></h3>
          <input type="text" id="pc-tag-search" placeholder="<?php echo esc_attr__('タグ名で検索（例: エアロ）', 'proshopwave'); ?>" />
          <div class="filterbar__options" id="pc-tag-results"></div>
        </div>
        <div class="filterbar__group">
          <h3><?php echo esc_html__('選択中', 'proshopwave'); ?></h3>
          <div class="filterbar__options" id="pc-tag-selected"></div>
        </div>
      </div>
      <div class="filterbar__actions">
        <button class="filterbar__btn js-reset-tag"><?php echo esc_html__('タグをクリア', 'proshopwave'); ?></button>
        <button class="filterbar__btn filterbar__btn--primary js-apply"><?php echo esc_html__('適用', 'proshopwave'); ?></button>
      </div>
    </div>
  </div>

  <!-- ブランド -->
  <div class="filterbar__dropdown" id="brand-dropdown" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="<?php echo esc_attr__('ブランドで選ぶ', 'proshopwave'); ?>">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3><?php echo esc_html__('ブランド/メーカー', 'proshopwave'); ?></h3>
          <div class="filterbar__options" id="pc-brand-maker">
            <?php if (!empty($brand_parents)) : ?>
              <?php foreach ($brand_parents as $bp) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>"><?php echo term_label($bp); ?></button>
              <?php endforeach; ?>
            <?php else : ?>
              <span class="muted"><?php echo esc_html__('ブランド用タクソノミーが未登録です（product_brand / pa_brand を想定）。', 'proshopwave'); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="filterbar__group">
          <h3><?php echo esc_html__('車種', 'proshopwave'); ?></h3>
          <?php foreach ($brand_parents as $bp) : ?>
            <div class="filterbar__options" id="pc-brand-model-<?php echo term_value($bp); ?>" data-parent="<?php echo term_value($bp); ?>">
              <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($mdl); ?>" data-parent="<?php echo term_value($bp); ?>"><?php echo term_label($mdl); ?></button>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="filterbar__group">
          <h3><?php echo esc_html__('型式', 'proshopwave'); ?></h3>
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
        <button class="filterbar__btn js-reset-brand"><?php echo esc_html__('ブランドをクリア', 'proshopwave'); ?></button>
        <button class="filterbar__btn filterbar__btn--primary js-apply"><?php echo esc_html__('適用', 'proshopwave'); ?></button>
      </div>
    </div>
  </div>
</div>

<!-- SP: モーダル -->
<div class="sp-only-block">
  <div class="filterbar__modal" id="filterbar-modal" aria-hidden="true">
    <div class="filterbar__modal-scrim js-close-modal" tabindex="-1"></div>
    <div class="filterbar__modal-panel" role="dialog" aria-label="<?php echo esc_attr__('フィルター', 'proshopwave'); ?>">
      <div class="filterbar__modal-header">
        <strong><?php echo esc_html__('条件で絞り込み', 'proshopwave'); ?></strong>
        <button class="filterbar__btn js-close-modal"><?php echo esc_html__('閉じる', 'proshopwave'); ?></button>
      </div>

      <details class="filterbar__accordion" open>
        <summary>
          <span><?php echo esc_html__('カテゴリーで選ぶ', 'proshopwave'); ?></span>
          <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </summary>
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
        <summary>
          <span><?php echo esc_html__('タグで選ぶ', 'proshopwave'); ?></span>
          <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="filterbar__accordion-panel">
          <div class="filterbar__options" id="sp-tag-popular">
            <?php foreach ($popular_tags as $tg) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($tg); ?>" data-label="<?php echo term_label($tg); ?>"><?php echo term_label($tg); ?></button>
            <?php endforeach; ?>
          </div>
          <input type="text" id="sp-tag-search" placeholder="<?php echo esc_attr__('タグ名で検索（例: エアロ）', 'proshopwave'); ?>" />
          <div class="filterbar__options" id="sp-tag-selected"></div>
        </div>
      </details>

      <?php if ($brand_tax) : ?>
        <details class="filterbar__accordion">
          <summary>
            <span><?php echo esc_html__('ブランドで選ぶ', 'proshopwave'); ?></span>
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </summary>
          <div class="filterbar__accordion-panel">
            <h4><?php echo esc_html__('ブランド/メーカー', 'proshopwave'); ?></h4>
            <div class="filterbar__options" id="sp-brand-maker">
              <?php foreach ($brand_parents as $bp) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>"><?php echo term_label($bp); ?></button>
              <?php endforeach; ?>
            </div>

            <h4><?php echo esc_html__('車種', 'proshopwave'); ?></h4>
            <?php foreach ($brand_parents as $bp) : ?>
              <div class="filterbar__options" id="sp-brand-model-<?php echo term_value($bp); ?>" data-parent="<?php echo term_value($bp); ?>">
                <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                  <button class="filterbar__chip" data-value="<?php echo term_value($mdl); ?>" data-parent="<?php echo term_value($bp); ?>"><?php echo term_label($mdl); ?></button>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>

            <h4><?php echo esc_html__('型式', 'proshopwave'); ?></h4>
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
        <button class="filterbar__btn" id="sp-reset"><?php echo esc_html__('リセット', 'proshopwave'); ?></button>
        <button class="filterbar__btn filterbar__btn--primary" id="sp-apply"><?php echo esc_html__('適用', 'proshopwave'); ?></button>
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
  data-tags='
  <?php
  // 全タグのスラッグ→ラベル辞書を JSON で埋め込む
  echo wp_json_encode($tag_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>'
  style="display:none">
</div>