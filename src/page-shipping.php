<?php

/**
 * Template Name: Shipping
 * Description: 配送・送料に関する案内ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="shipping section">
  <div class="shipping__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="shipping__header">
          <h1 class="shipping__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="shipping__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="shipping__content">
          <?php the_content(); ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
?>