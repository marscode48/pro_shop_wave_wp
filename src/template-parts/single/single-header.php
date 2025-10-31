<?php

/**
 * Single Post Header
 * Displays the title, date, category, and featured image for single posts.
 */
?>
<header class="single-blog__header">
  <?php if (has_post_thumbnail()) : ?>
    <div class="single-blog__thumbnail">
      <?php the_post_thumbnail('large'); ?>
    </div>
  <?php endif; ?>

  <div class="single-blog__meta">
    <div class="single-blog__category">
      <?php the_category(' '); ?>
    </div>
    <h1 class="single-blog__title"><?php the_title(); ?></h1>
    <?php get_template_part('template-parts/meta/meta', 'date', ['class' => 'single-blog__date']); ?>
  </div>
</header>