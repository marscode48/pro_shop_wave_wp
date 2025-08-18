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
function psw_get_terms_safe($args)
{
  $terms = get_terms($args);
  return (! is_wp_error($terms) && ! empty($terms) && is_array($terms)) ? $terms : [];
}

// --------------------------------------------------
// 1) カテゴリ：親=parts/apparel を優先、無ければ親カテゴリ一覧
// --------------------------------------------------
$parent_slugs = ['parts', 'apparel'];
$parent_terms = psw_get_terms_safe([
  'taxonomy'   => 'product_cat',
  'hide_empty' => true,
  'slug'       => $parent_slugs,
]);

if (count($parent_terms) < 2) {
  // フォールバック：親カテゴリ（parent = 0）を列挙
  $parent_terms = psw_get_terms_safe([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
}

// 親→子マップを構築
$children_map = [];
foreach ($parent_terms as $p) {
  $children_map[$p->slug] = psw_get_terms_safe([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => (int) $p->term_id,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
}

// --------------------------------------------------
// 2) タグ：人気順トップ10
// --------------------------------------------------
$popular_tags = psw_get_terms_safe([
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
$brand_terms = [];
if ($brand_tax) {
  $brand_terms = psw_get_terms_safe([
    'taxonomy'   => $brand_tax,
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 20,
  ]);
}

// 補助：表示テキスト
function psw_term_label($t)
{
  return esc_html($t->name);
}
function psw_term_value($t)
{
  return esc_attr($t->slug);
}
?>

<!-- フィルターバー（ピル） -->
<div class="filterbar">
  <button class="filterbar__pill js-open-dropdown" data-target="cat-dd" aria-expanded="false">
    <span class="filterbar__pill-label">カテゴリー</span>
    <span class="filterbar__pill-value" id="pill-cat">All</span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="tag-dd" aria-expanded="false">
    <span class="filterbar__pill-label">タグ</span>
    <span class="filterbar__pill-value" id="pill-tag">All</span>
  </button>
  <button class="filterbar__pill js-open-dropdown" data-target="brand-dd" aria-expanded="false">
    <span class="filterbar__pill-label">ブランド</span>
    <span class="filterbar__pill-value" id="pill-brand">All</span>
  </button>
  <button class="filterbar__open only-sp js-open-modal">フィルター</button>
</div>

<!-- PC: ドロップダウン パネル群 -->
<div class="only-pc">
  <!-- カテゴリ -->
  <div class="filterbar__dropdown" id="cat-dd" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="カテゴリで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>親カテゴリ</h3>
          <div class="filterbar__options" id="pc-cat-parent">
            <?php foreach ($parent_terms as $pt) : ?>
              <button class="filterbar__chip" data-value="<?php echo psw_term_value($pt); ?>"><?php echo psw_term_label($pt); ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <?php foreach ($parent_terms as $pt) : ?>
          <div class="filterbar__group">
            <h3>子カテゴリ（<?php echo psw_term_label($pt); ?>）</h3>
            <div class="filterbar__options" id="pc-cat-children-<?php echo psw_term_value($pt); ?>">
              <?php foreach ($children_map[$pt->slug] as $ct) : ?>
                <button class="filterbar__chip" data-value="<?php echo psw_term_value($ct); ?>"><?php echo psw_term_label($ct); ?></button>
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
  <div class="filterbar__dropdown" id="tag-dd" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="タグで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>人気タグ</h3>
          <div class="filterbar__options" id="pc-tag-popular">
            <?php foreach ($popular_tags as $tg) : ?>
              <button class="filterbar__chip" data-value="<?php echo psw_term_value($tg); ?>"><?php echo psw_term_label($tg); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filterbar__group">
          <h3>検索</h3>
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
  <div class="filterbar__dropdown" id="brand-dd" aria-hidden="true">
    <div class="filterbar__panel" role="dialog" aria-label="ブランドで選ぶ">
      <div class="filterbar__grid">
        <div class="filterbar__group">
          <h3>ブランド/メーカー</h3>
          <div class="filterbar__options" id="pc-brand-maker">
            <?php foreach ($brand_terms as $bt) : ?>
              <button class="filterbar__chip" data-value="<?php echo psw_term_value($bt); ?>" data-tax="<?php echo esc_attr($brand_tax); ?>"><?php echo psw_term_label($bt); ?></button>
            <?php endforeach; ?>
            <?php if (empty($brand_terms)) : ?>
              <span class="muted">ブランド用タクソノミーが未登録です（product_brand / pa_brand を想定）。</span>
            <?php endif; ?>
          </div>
        </div>
        <!-- もし model/chassis 等のタクソノミーがあれば同様にセクション追加 -->
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

    <details class="filterbar__acc" open>
      <summary>カテゴリーで選ぶ</summary>
      <div class="filterbar__acc-panel">
        <label style="display:block; margin:.3em 0 .4em">親</label>
        <div class="filterbar__options" id="sp-cat-parent">
          <?php foreach ($parent_terms as $pt) : ?>
            <button class="filterbar__chip" data-value="<?php echo psw_term_value($pt); ?>"><?php echo psw_term_label($pt); ?></button>
          <?php endforeach; ?>
        </div>
        <label style="display:block; margin:1em 0 .4em">子</label>
        <div class="filterbar__options" id="sp-cat-children">
          <!-- JSで parent 選択に応じて children を描画 -->
          <?php // 初期は空。JSで children_map を使い埋める想定 
          ?>
        </div>
      </div>
    </details>

    <details class="filterbar__acc">
      <summary>タグで選ぶ</summary>
      <div class="filterbar__acc-panel">
        <input type="text" id="sp-tag-search" placeholder="タグ検索" style="width:100%; padding:.6em; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:#000; color:#fff" />
        <div class="filterbar__options" id="sp-tag-popular" style="margin-top:8px">
          <?php foreach ($popular_tags as $tg) : ?>
            <button class="filterbar__chip" data-value="<?php echo psw_term_value($tg); ?>"><?php echo psw_term_label($tg); ?></button>
          <?php endforeach; ?>
        </div>
        <div class="filterbar__options" id="sp-tag-selected" style="margin-top:8px"></div>
      </div>
    </details>

    <?php if ($brand_tax) : ?>
      <details class="filterbar__acc">
        <summary>ブランドで選ぶ</summary>
        <div class="filterbar__acc-panel">
          <label style="display:block; margin:.3em 0 .4em">ブランド</label>
          <div class="filterbar__options" id="sp-brand-maker">
            <?php foreach ($brand_terms as $bt) : ?>
              <button class="filterbar__chip" data-value="<?php echo psw_term_value($bt); ?>" data-tax="<?php echo esc_attr($brand_tax); ?>"><?php echo psw_term_label($bt); ?></button>
            <?php endforeach; ?>
          </div>
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
?>
<div id="filterbar-dataset"
  data-children='<?php echo wp_json_encode($children_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
  data-brand-tax="<?php echo esc_attr($brand_tax); ?>"
  style="display:none"></div>