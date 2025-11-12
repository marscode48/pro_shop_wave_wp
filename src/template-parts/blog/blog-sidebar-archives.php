<?php

/**
 * Blog Sidebar: Archives
 *
 * 月別アーカイブ（デフォルトは直近12か月）をサイドバーに表示します。
 * リスト形式／セレクト形式はフィルタで切り替え可能です。
 *
 * フィルタ一覧:
 * - blog_sidebar_archives_title (string) : 見出しテキスト
 * - blog_sidebar_archives_mode  (string) : 'list' | 'select' 表示モード
 * - blog_sidebar_archives_args  (array)  : wp_get_archives() に渡す引数
 */

if (! defined('ABSPATH')) {
  exit;
}

// 見出し
$title = apply_filters('blog_sidebar_archives_title', __('アーカイブ', 'proshopwave'));

// 表示モード: 'list' か 'select'
$mode  = apply_filters('blog_sidebar_archives_mode', 'list');

// 取得引数（デフォルト: 月別、12件、件数表示あり）
$args = wp_parse_args(
  apply_filters('proshopwave_blog_sidebar_archives_args', []),
  [
    'type'            => 'monthly',   // yearly | monthly | weekly | daily | postbypost | alpha
    'limit'           => 12,
    'show_post_count' => true,
    'order'           => 'DESC',
    'echo'            => 0,           // 文字列で受け取って加工
    'format'          => 'html',      // list 用の既定
  ]
);

// データ存在チェック用（最小取得）。
$has_archives = wp_get_archives([
  'type'  => $args['type'],
  'limit' => 1,
  'echo'  => 0,
]);

if (empty($has_archives)) {
  return; // アーカイブが無ければ表示しない
}
?>

<aside class="blog-sidebar__section  blog-sidebar__archives" aria-labelledby="blog-sidebar-archives-title">
  <h2 id="blog-sidebar-archives-title" class="blog-sidebar__title">
    <?php echo esc_html($title); ?>
  </h2>

  <?php if ('select' === $mode) : ?>
    <?php
    // セレクト用: option で取得
    $options = wp_get_archives(array_merge($args, ['format' => 'option']));
    ?>
    <form class="blog-sidebar__archives-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
      <label class="screen-reader-text" for="archive-dropdown">
        <?php echo esc_html__('アーカイブを選択', 'proshopwave'); ?>
      </label>
      <div class="blog-sidebar__select-wrap">
        <select
          id="archive-dropdown"
          class="blog-sidebar__archives-select"
          name="archive-dropdown"
          aria-label="<?php echo esc_attr__('アーカイブ選択', 'proshopwave'); ?>"
          onchange="if (this.value) { window.location.href=this.value; }">
          <option value="">
            <?php echo esc_html__('月を選択…', 'proshopwave'); ?>
          </option>
          <?php
          // wp_get_archives() は option 要素を返すため、そのまま出力
          echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          ?>
        </select>
        <i class="fas fa-chevron-down" aria-hidden="true"></i>
      </div>
    </form>
  <?php else : ?>
    <?php $list_items = wp_get_archives($args); ?>
    <ul class="blog-sidebar__archives-list" role="list">
      <?php echo $list_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_archives() は li を返す 
      ?>
    </ul>
  <?php endif; ?>
</aside>