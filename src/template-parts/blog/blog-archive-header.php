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
  $title = __('blog', 'proshopwave');
} elseif (is_category()) {
  // カテゴリーアーカイブ
  $title       = sprintf(__('「%s」カテゴリーの記事', 'proshopwave'), single_cat_title('', false));
  $description = term_description();
} elseif (is_tag()) {
  // タグアーカイブ
  $title       = sprintf(__('「%s」タグの記事', 'proshopwave'), single_tag_title('', false));
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
    __('%s年の記事', 'proshopwave'),
    get_the_date('Y')
  );
} elseif (is_month()) {
  // 月別アーカイブ
  $title = sprintf(
    __('%s年%s月の記事', 'proshopwave'),
    get_the_date('Y'),
    get_the_date('n')
  );
} elseif (is_day()) {
  // 日別アーカイブ
  $title = sprintf(
    __('%s年%s月%s日の記事', 'proshopwave'),
    get_the_date('Y'),
    get_the_date('n'),
    get_the_date('j')
  );
} elseif (is_author()) {
  // 著者アーカイブ
  $author = get_queried_object();

  if ($author) {
    $title       = sprintf(__('投稿者「%s」の記事', 'proshopwave'), $author->display_name);
    $description = get_the_author_meta('description', $author->ID);
  }
}

// どの条件にも当てはまらない場合のフォールバック
if ('' === $title) {
  $title = __('blog', 'proshopwave');
}

if ($title) : ?>
  <header class="blog-archive-header">
    <h1 class="blog-archive__title section__title fadeup">
      <?php echo esc_html($title); ?>
    </h1>

    <?php if (! empty($description)) : ?>
      <div class="blog-archive__description section__text fadeup">
        <?php echo wp_kses_post(wpautop($description)); ?>
      </div>
    <?php endif; ?>
  </header>
<?php endif; ?>