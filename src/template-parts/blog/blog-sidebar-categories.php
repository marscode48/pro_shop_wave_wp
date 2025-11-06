<?php

/**
 * Blog Sidebar: Categories list
 *
 * Template part to display blog categories in the sidebar.
 * Location: template-parts/blog/blog-sidebar-categories.php
 *
 * 基本仕様:
 * - 見出しはフィルタで変更可能: `proshopwave_blog_sidebar_categories_heading`
 * - 表示件数はフィルタで変更可能: `proshopwave_blog_sidebar_categories_count`
 * - 空カテゴリは非表示
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

$heading = apply_filters('proshopwave_blog_sidebar_categories_heading', esc_html__('カテゴリー', 'proshopwave'));
$count   = (int) apply_filters('proshopwave_blog_sidebar_categories_count', 10);

$categories = get_categories([
  'orderby'    => 'name',
  'order'      => 'ASC',
  'number'     => $count,
  'hide_empty' => true,
  'exclude'    => 1, // “未分類”(Uncategorized, ID=1) を除外
]);
?>

<aside class="blog-sidebar__section blog-sidebar__categories" aria-labelledby="blog-sidebar-categories-title">
  <h2 id="blog-sidebar-categories-title" class="blog-sidebar__title">
    <?php echo esc_html($heading); ?>
  </h2>

  <?php if (! empty($categories)) : ?>
    <ul class="blog-sidebar__categories-list" role="list">
      <?php foreach ($categories as $cat) : ?>
        <li class="blog-sidebar__categories-item">
          <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="blog-sidebar__categories-link">
            <?php echo esc_html($cat->name); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p class="blog-sidebar__empty"><?php echo esc_html__('カテゴリーはありません。', 'proshopwave'); ?></p>
  <?php endif; ?>
</aside>