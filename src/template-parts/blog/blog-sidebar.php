<?php

/**
 * Blog Sidebar (おすすめ/カテゴリ/アーカイブ)
 *
 * テンプレート場所: template-parts/blog/blog-sidebar.php
 * 本サイドバーは single.php / archive.php / search.php 等から読み込む想定。
 * BEMクラス: .blog-sidebar をルートに採用。
 *
 * @package proshopwave
 */
?>

<aside class="blog-sidebar fadeup" role="complementary" aria-label="<?php echo esc_attr__( 'サイドバー', 'proshopwave' ); ?>">
  <div class="blog-sidebar__inner">
    <?php
    // -----------------------------
    // おすすめ記事（手動ピック or 人気記事など）
    // -----------------------------
    get_template_part('template-parts/blog/blog-sidebar', 'recommend');

    // -----------------------------
    // カテゴリ一覧
    // -----------------------------
    get_template_part('template-parts/blog/blog-sidebar', 'categories');

    // -----------------------------
    // 月別アーカイブ
    // -----------------------------
    get_template_part('template-parts/blog/blog-sidebar', 'archives');
    ?>
  </div>
</aside>