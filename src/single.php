<?php
/**
 * The template for displaying single blog posts
 *
 * @package PRO_SHOP_WAVE
 */
?>

<?php get_header(); ?>

<main class="single-blog section">
  <div class="single-blog__inner l-container l-container--narrow">
    <?php get_template_part('template-parts/breadcrumb/breadcrumb'); ?>
    <div class="single-blog__body">
      <header class="single-blog__header">
        <h1 class="single-blog__title section__title"><?php the_title(); ?></h1>
        <div class="single-blog__meta">
          <time datetime="<?php the_time('c'); ?>"><?php the_time('Y.m.d'); ?></time>
          <span>｜</span>
          <span><?php the_category(', '); ?></span>
        </div>
        <?php if (has_post_thumbnail()) : ?>
          <div class="single-blog__thumbnail">
            <?php the_post_thumbnail('large'); ?>
          </div>
        <?php endif; ?>
      </header>
    </div>
    <div class="single-blog__content">
      <?php the_content(); ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>