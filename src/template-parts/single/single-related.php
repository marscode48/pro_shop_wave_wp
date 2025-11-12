<?php

/**
 * Related posts section for single post pages
 *
 * 出力ポリシー:
 *  - 同一カテゴリの投稿を最大6件取得（該当なしの場合はタグで代替）
 *  - 現在の投稿は除外
 *  - スティッキーポスト無視、ページネーション不要
 *  - `template-parts/card/card-blog.php` を使って各カードを描画
 *  - 投稿が見つからない場合はセクション自体を出力しない（空表示回避）
 *
 * @package PRO_SHOP_WAVE
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

$current_id = get_the_ID();

// 1) 同一カテゴリのID配列を取得
$cat_ids = wp_get_post_categories($current_id);

// 2) タクソノミークエリの組み立て（カテゴリ優先、無ければタグ）
$tax_query = [];
if (! empty($cat_ids)) {
  $tax_query[] = [
    'taxonomy' => 'category',
    'field'    => 'term_id',
    'terms'    => $cat_ids,
  ];
} else {
  // カテゴリが無い場合はタグで代替
  $tag_ids = wp_get_post_terms($current_id, 'post_tag', ['fields' => 'ids']);
  if (! empty($tag_ids)) {
    $tax_query[] = [
      'taxonomy' => 'post_tag',
      'field'    => 'term_id',
      'terms'    => $tag_ids,
    ];
  }
}

// タクソノミーが一つも無ければ中断
if (empty($tax_query)) {
  return;
}

$args = [
  'post_type'           => 'post',
  'posts_per_page'      => 6,
  'post__not_in'        => [$current_id],
  'ignore_sticky_posts' => true,
  'no_found_rows'       => true,
  'orderby'             => 'date',
  'order'               => 'DESC',
  'tax_query'           => [
    'relation' => 'AND',
    $tax_query[0],
  ],
];

$related = new WP_Query($args);

if (! $related->have_posts()) {
  wp_reset_postdata();
  return; // 何も出力しない
}
?>

<section class="single-blog__related" aria-labelledby="related-heading">
  <div class="l-container">
    <h2 id="related-heading" class="single-blog__related-title">
      <?php echo esc_html__('関連記事', 'proshopwave'); ?>
    </h2>

    <div class="single-blog__related-list blog-grid card-list--blog">
      <?php while ($related->have_posts()) : $related->the_post(); ?>
        <?php get_template_part('template-parts/card/card-blog'); ?>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<?php
wp_reset_postdata();
