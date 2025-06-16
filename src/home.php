<?php
/**
 * The template for displaying the blog index (home.php)
 *
 * @package PRO_SHOP_WAVE
 */
?>

<?php get_header(); ?>

<main class="blog-archive section">
  <div class="blog-archive__inner">

    <h1 class="blog-archive__title section__title">BLOG</h1>

    <div class="card-list--blog">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <?php get_template_part('template-parts/card/card-blog'); ?>
      <?php endwhile; else : ?>
        <p>記事が見つかりませんでした。</p>
      <?php endif; ?>
    </div>

    <div class="blog-archive__pagination">
      <?php
        the_posts_pagination([
          'mid_size'           => 1,
          'prev_text'          => '<i class="fas fa-chevron-left"></i>',
          'next_text'          => '<i class="fas fa-chevron-right"></i>',
          'screen_reader_text' => 'ページネーション',
          'before_page_number' => '<span class="screen-reader-text">ページ </span>',
        ]);
      ?>
    </div>

  </div>
</main>

<?php get_footer(); ?>