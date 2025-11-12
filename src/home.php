<?php

/**
 * The template for displaying the blog index (home.php)
 *
 * @package PRO_SHOP_WAVE
 */
?>

<?php get_header(); ?>

<main class="blog-archive section">
  <div class="blog-archive__inner  l-container l-container--wide">
    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');

    // アーカイブヘッダー（タイトル・説明）
    get_template_part('template-parts/blog/blog-archive-header');

    // 投稿ループ＋ページネーション
    get_template_part('template-parts/blog/blog-archive-loop');
    ?>
  </div>
</main>

<?php get_footer(); ?>