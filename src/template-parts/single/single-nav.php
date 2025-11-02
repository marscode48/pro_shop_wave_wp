<?php

/**
 * Single: prev/next navigation (同カテゴリ優先・無い時は全体)
 * @package PRO_SHOP_WAVE
 */

if (! defined('ABSPATH')) exit;

// 同カテゴリ優先で取得
$prev_same = get_previous_post(true); // 同カテゴリ内の前記事
$next_same = get_next_post(true);     // 同カテゴリ内の次記事

// 同カテゴリが無い場合の fallback
$prev_any  = get_previous_post(false);
$next_any  = get_next_post(false);

$prev_post = $prev_same ?: $prev_any;
$next_post = $next_same ?: $next_any;
?>

<nav class="single-blog__nav" aria-label="<?php echo esc_attr__('記事ナビゲーション', 'proshopwave'); ?>">

  <div class="single-blog__nav-prev">
    <?php if ($prev_post): ?>
      <a class="single-blog__nav-link single-blog__nav-link--prev"
        href="<?php echo esc_url(get_permalink($prev_post)); ?>">
        <span class="single-blog__nav-arrow single-blog__nav-arrow--prev" aria-hidden="true">
          <i class="fa-solid fa-chevron-left"></i>
        </span>
        <span class="single-blog__nav-title">
          <?php echo esc_html(get_the_title($prev_post)); ?>
        </span>
      </a>
    <?php endif; ?>
  </div>

  <div class="single-blog__nav-next">
    <?php if ($next_post): ?>
      <a class="single-blog__nav-link single-blog__nav-link--next"
        href="<?php echo esc_url(get_permalink($next_post)); ?>">
        <span class="single-blog__nav-arrow single-blog__nav-arrow--next" aria-hidden="true">
          <i class="fa-solid fa-chevron-right"></i>
        </span>
        <span class="single-blog__nav-title">
          <?php echo esc_html(get_the_title($next_post)); ?>
        </span>
      </a>
    <?php endif; ?>
  </div>

</nav>