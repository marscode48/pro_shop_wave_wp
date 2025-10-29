<?php

/**
 * Blog Sidebar: Recommend list
 *
 * Template part to display a list of recommended blog posts in the sidebar.
 * Location: template-parts/blog/blog-sidebar-recommend.php
 *
 * 基本仕様:
 * - 見出しはフィルタで変更可能: `proshopwave_blog_sidebar_recommend_heading`
 * - 取得件数はフィルタで変更可能: `proshopwave_blog_sidebar_recommend_count`
 * - 推奨ターム(カテゴリ/タグ)のスラッグ: recommend / recommended / pickup / featured
 *   上記いずれかのタームが存在すればそれを優先、なければ最新記事にフォールバック
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// 見出しと件数(フィルタで上書き可能)
$heading = apply_filters('proshopwave_blog_sidebar_recommend_heading', esc_html__('おすすめ記事', 'proshopwave'));
$count   = (int) apply_filters('proshopwave_blog_sidebar_recommend_count', 3);

// "おすすめ"用の候補ターム(カテゴリ/タグ)を探索
$tax_query  = [];
$slug_pool  = ['recommend', 'recommended', 'pickup', 'featured'];
$maybe_terms = get_terms([
  'taxonomy'   => ['category', 'post_tag'],
  'slug'       => $slug_pool,
  'hide_empty' => true,
]);

if (! is_wp_error($maybe_terms) && ! empty($maybe_terms)) {
  $tax_query = ['relation' => 'OR'];
  foreach ($maybe_terms as $t) {
    $tax_query[] = [
      'taxonomy'         => $t->taxonomy,
      'field'            => 'term_id',
      'terms'            => [$t->term_id],
      'include_children' => true,
    ];
  }
}

// WP_Query の引数を組み立て
$args = [
  'post_type'           => 'post',
  'posts_per_page'      => max(1, $count),
  'ignore_sticky_posts' => true,
  'no_found_rows'       => true,
];

if (! empty($tax_query)) {
  $args['tax_query'] = $tax_query; // タームが見つかったときのみ絞り込み
}

$recommend_query = new WP_Query($args);
?>

<aside class="blog-sidebar__section blog-sidebar__recommend" aria-labelledby="blog-sidebar-recommend-title">
  <h2 id="blog-sidebar-recommend-title" class="blog-sidebar__title">
    <?php echo esc_html($heading); ?>
  </h2>

  <?php if ($recommend_query->have_posts()) : ?>
    <ul class="blog-sidebar__recommend-list" role="list">
      <?php while ($recommend_query->have_posts()) : $recommend_query->the_post(); ?>
        <li class="blog-sidebar__recommend-item">
          <?php // 既存のブログカード部品を再利用
          get_template_part('template-parts/card/card-blog'); ?>
        </li>
      <?php endwhile;
      wp_reset_postdata(); ?>
    </ul>
  <?php else : ?>
    <p class="blog-sidebar__empty"><?php echo esc_html__('おすすめ記事はありません。', 'proshopwave'); ?></p>
  <?php endif; ?>
</aside>