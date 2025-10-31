<?php

/**
 * Single: prev/next navigation (同カテゴリ優先・無い場合は全体)
 * @package PRO_SHOP_WAVE
 */

if (! defined('ABSPATH')) exit;

$prev_same = get_previous_post(true);
$next_same = get_next_post(true);
?>
<nav class="single-blog__nav" aria-label="<?php echo esc_attr__('記事ナビゲーション', 'proshopwave'); ?>">
  <div class="single-blog__nav-prev">
    <?php
    if ($prev_same) {
      previous_post_link('%link', '← %title', true);
    } else {
      // 同カテゴリが無い時のみ全体での前記事
      previous_post_link('%link', '← %title', false);
    }
    ?>
  </div>
  <div class="single-blog__nav-next">
    <?php
    if ($next_same) {
      next_post_link('%link', '%title →', true);
    } else {
      // 同カテゴリが無い時のみ全体での次記事
      next_post_link('%link', '%title →', false);
    }
    ?>
  </div>
</nav>