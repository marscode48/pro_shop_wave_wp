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
  <button class="filterbar__open sp-only js-open-modal">フィルター</button>
</div>

<!-- PC: ドロップダウン パネル群 -->
<div class="pc-only">
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
        <button class="filterbar__btn js-reset">リセット</button>
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
          <input type="text" id="pc-tag-search" placeholder="タグ名で検索（例: drift, aero）" style="width:100%; padding:.6em; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:#000; color:#fff" />
          <div class="filterbar__options" id="pc-tag-results" style="margin-top:8px"></div>
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
                <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>" data-tax="<?php echo esc_attr($brand_tax); ?>"><?php echo term_label($bp); ?></button>
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
        <div class="filterbar__options" id="sp-tag-popular" style="margin-top:8px">
          <?php foreach ($popular_tags as $tg) : ?>
            <button class="filterbar__chip" data-value="<?php echo term_value($tg); ?>" data-label="<?php echo term_label($tg); ?>"><?php echo term_label($tg); ?></button>
          <?php endforeach; ?>
        </div>
        <input type="text" id="sp-tag-search" placeholder="タグ検索" style="width:100%; padding:.6em; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:#000; color:#fff" />
        <div class="filterbar__options" id="sp-tag-selected" style="margin-top:8px"></div>
      </div>
    </details>

    <?php if ($brand_tax) : ?>
      <details class="filterbar__accordion">
        <summary>ブランドで選ぶ</summary>
        <div class="filterbar__accordion-panel">
          <label style="display:block; margin:.3em 0 .4em">ブランド/メーカー</label>
          <div class="filterbar__options" id="sp-brand-maker">
            <?php foreach ($brand_parents as $bp) : ?>
              <button class="filterbar__chip" data-value="<?php echo term_value($bp); ?>" data-tax="<?php echo esc_attr($brand_tax); ?>"><?php echo term_label($bp); ?></button>
            <?php endforeach; ?>
          </div>

          <label style="display:block; margin:1em 0 .4em">車種</label>
          <?php foreach ($brand_parents as $bp) : ?>
            <div class="filterbar__options" id="sp-brand-model-<?php echo term_value($bp); ?>" data-parent="<?php echo term_value($bp); ?>">
              <?php foreach (($brand_models_map[$bp->slug] ?? []) as $mdl) : ?>
                <button class="filterbar__chip" data-value="<?php echo term_value($mdl); ?>" data-parent="<?php echo term_value($bp); ?>"><?php echo term_label($mdl); ?></button>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <label style="display:block; margin:1em 0 .4em">型式</label>
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

<?php
// --------------------------------------------------
// JSへ children_map を渡したい場合は、以下のように data-* で埋め込む手もあります。
// 本テンプレでは、カテゴリの子一覧を data 属性として JSON で吐き出します。
// front側の JS で JSON.parse して利用してください。
// --------------------------------------------------
$children_payload = [];
foreach ($parent_terms as $pt) {
  $children_payload[$pt->slug] = array_map(function ($ct) {
    return ['value' => $ct->slug, 'label' => $ct->name];
  }, $children_map[$pt->slug]);
}

$brand_payload = ['parents' => [], 'models' => [], 'chassis' => []];
foreach ($brand_parents as $bp) {
  $brand_payload['parents'][] = ['value' => $bp->slug, 'label' => $bp->name];
  $mdlList = $brand_models_map[$bp->slug] ?? [];
  $brand_payload['models'][$bp->slug] = array_map(function ($m) {
    return ['value' => $m->slug, 'label' => $m->name];
  }, $mdlList);
  foreach ($mdlList as $m) {
    $chsList = $brand_chassis_map[$m->slug] ?? [];
    $brand_payload['chassis'][$m->slug] = array_map(function ($c) {
      return ['value' => $c->slug, 'label' => $c->name];
    }, $chsList);
  }
}
?>
<div id="filterbar-dataset"
  data-children='<?php echo wp_json_encode($children_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
  data-brand-tax="<?php echo esc_attr($brand_tax); ?>"
  data-brand='<?php echo wp_json_encode($brand_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
  style="display:none"></div>