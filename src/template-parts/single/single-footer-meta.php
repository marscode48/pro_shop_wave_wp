<?php

/**
 * 投稿フッターメタ（カテゴリ / タグ）
 *
 * - BEM: .single-blog__footer 配下に .post-meta を配置
 * - カテゴリ・タグが存在しない場合は該当ブロックを非表示
 */

// セキュリティ: 直接アクセス防止
if (! defined('ABSPATH')) {
  exit;
}

// 現在投稿のカテゴリを取得（タグは $tags_html 側で出力）
$category_list = get_the_category_list(' '); // スペース区切り

// カテゴリもタグも存在しない場合は出力しない
if (empty($category_list) && empty(get_the_tags())) {
  return;
}
?>

<section class="single-blog__footer" aria-labelledby="post-meta-heading">
  <h2 id="post-meta-heading" class="screen-reader-text"><?php echo esc_html__('投稿メタ情報', 'proshopwave'); ?></h2>

  <?php if (! empty($category_list)) : ?>
    <div class="post-meta post-meta--cats">
      <span class="post-meta__label"><?php echo esc_html__('カテゴリー', 'proshopwave'); ?>:</span>
      <span class="post-meta__items"><?php echo wp_kses_post($category_list); ?></span>
    </div>
  <?php endif; ?>

  <?php
  $post_tags = get_the_tags();
  if ($post_tags) :
    $tags_html = array_map(function ($tag) {
      $url  = get_tag_link($tag->term_id);
      $name = '#' . $tag->name;
      return '<a class="post-meta__tag" href="' . esc_url($url) . '">' . esc_html($name) . '</a>';
    }, $post_tags);
  ?>
    <div class="post-meta post-meta--tags">
      <span class="post-meta__label"><?php echo esc_html__('タグ', 'proshopwave'); ?>:</span>
      <span class="post-meta__items">
        <?php echo implode(' ', $tags_html); ?>
      </span>
    </div>
  <?php endif; ?>
</section>