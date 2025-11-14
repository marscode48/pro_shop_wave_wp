<?php

/**
 * Template part: Blog archive header
 *
 * Handles the archive page title and optional description
 * for blog-related archives (home, category, tag, date, author, etc.).
 *
 * @package proshopwave
 */

$title       = '';
$description = '';

if (is_home() && ! is_front_page()) {
  // ブログインデックス（/blog 等）
  $title = __('Blog', 'proshopwave');
} elseif (is_category()) {
  // カテゴリーアーカイブ
  $title       = sprintf(__('カテゴリー: %s', 'proshopwave'), single_cat_title('', false));
  $description = term_description();
} elseif (is_tag()) {
  // タグアーカイブ
  $title       = sprintf(__('タグ: %s', 'proshopwave'), single_tag_title('', false));
  $description = term_description();
} elseif (is_tax()) {
  // カスタムタクソノミー用（必要に応じて使用）
  $title       = single_term_title('', false);
  $description = term_description();
} elseif (is_post_type_archive()) {
  // 投稿タイプアーカイブ
  $title = post_type_archive_title('', false);
} elseif (is_year()) {
  // 年別アーカイブ
  $title = sprintf(
    __('年: %s', 'proshopwave'),
    get_the_date(_x('Y', 'yearly archives date format', 'proshopwave'))
  );
} elseif (is_month()) {
  // 月別アーカイブ
  $title = sprintf(
    __('月: %s', 'proshopwave'),
    get_the_date(_x('F Y', 'monthly archives date format', 'proshopwave'))
  );
} elseif (is_day()) {
  // 日別アーカイブ
  $title = sprintf(
    __('日: %s', 'proshopwave'),
    get_the_date(_x('F j, Y', 'daily archives date format', 'proshopwave'))
  );
} elseif (is_author()) {
  // 著者アーカイブ
  $author = get_queried_object();

  if ($author) {
    $title       = sprintf(__('著者: %s', 'proshopwave'), $author->display_name);
    $description = get_the_author_meta('description', $author->ID);
  }
}

// どの条件にも当てはまらない場合のフォールバック
if ('' === $title) {
  $title = __('ブログ', 'proshopwave');
}

if ($title) : ?>
  <header class="blog-archive-header">
    <h1 class="blog-archive__title section__title fadeup">
      <?php echo esc_html($title); ?>
    </h1>

    <?php if (! empty($description)) : ?>
      <div class="blog-archive__description section__text">
        <?php echo wp_kses_post(wpautop($description)); ?>
      </div>
    <?php endif; ?>
  </header>
<?php endif; ?>