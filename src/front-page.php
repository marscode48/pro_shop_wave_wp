<?php
/**
 * Template Name: Front Page
 * @package PRO_SHOP_WAVE
 */
get_header();
?>

<main class="main">

  <?php get_template_part('template-parts/section/section', 'hero'); ?>

  <div class="section-group">
    <div class="section-bg">
      <picture class="section-bg__picture parallax" data-speed="35">
        <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/bg_newparts_blog_parallax_pc.webp'); ?>" type="image/webp">
        <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/bg_newparts_blog_parallax_pc.jpg'); ?>" type="image/jpeg">
        <source srcset="<?php echo get_theme_file_uri('images/bg_newparts_blog_parallax_sp.webp'); ?>" type="image/webp">
        <source srcset="<?php echo get_theme_file_uri('images/bg_newparts_blog_parallax_sp.jpg'); ?>" type="image/jpeg">
        <img src="<?php echo get_theme_file_uri('images/bg_newparts_blog_parallax_sp.jpg'); ?>" alt="背景画像" class="section-bg__img">
      </picture>
    </div>

    <?php get_template_part('template-parts/section/section', 'new-arrivals'); ?>
    <?php get_template_part('template-parts/section/section', 'custom-parts'); ?>
    <?php get_template_part('template-parts/section/section', 'apparel'); ?>
    <?php get_template_part('template-parts/section/section', 'blog'); ?>

  </div>

  <?php get_template_part('template-parts/section/section', 'sns'); ?>

</main>

<?php get_footer(); ?>