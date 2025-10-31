<?php

/**
 * 投稿フッターメタ（カテゴリ / タグ）
 *
 * - single.php から読み込まれる想定のテンプレート
 * - BEM: .single-blog__footer 配下に .post-meta を配置
 * - カテゴリ・タグが存在しない場合は該当ブロックを非表示
 */

// セキュリティ: 直接アクセス防止
if (! defined('ABSPATH')) {
  exit;
}

// 現在投稿のカテゴリとタグを取得
$category_list = get_the_category_list(', '); // 例: <a>Cat1</a>, <a>Cat2</a>
$tag_list      = get_the_tag_list('', ', ');  // 例: <a>tag1</a>, <a>tag2</a>

// 何もなければ出力しない
if (empty($category_list) && empty($tag_list)) {
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

  <?php if (! empty($tag_list)) : ?>
    <div class="post-meta post-meta--tags">
      <span class="post-meta__label"><?php echo esc_html__('タグ', 'proshopwave'); ?>:</span>
      <span class="post-meta__items"><?php echo wp_kses_post($tag_list); ?></span>
    </div>
  <?php endif; ?>
</section>