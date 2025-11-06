<?php

/**
 * Template part: Blog archive loop
 *
 * Outputs the post loop and pagination for blog-related archives.
 *
 * @package proshopwave
 */
?>

<?php if (have_posts()) : ?>
  <div class="card-list--blog">
    <?php while (have_posts()) : the_post(); ?>
      <?php get_template_part('template-parts/card/card-blog'); ?>
    <?php endwhile; ?>
  </div>

  <div class="blog-archive__pagination">
    <?php
    the_posts_pagination([
      'mid_size'           => 1,
      'prev_text'          => '<i class="fas fa-chevron-left" aria-hidden="true"></i>',
      'next_text'          => '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
      'screen_reader_text' => esc_html__('Posts navigation', 'proshopwave'),
      'before_page_number' => '<span class="screen-reader-text">' . esc_html__('Page', 'proshopwave') . ' </span>',
    ]);
    ?>
  </div>
<?php else : ?>
  <p class="blog-archive__not-found">
    <?php esc_html_e('記事が見つかりませんでした。', 'proshopwave'); ?>
  </p>
<?php endif; ?>