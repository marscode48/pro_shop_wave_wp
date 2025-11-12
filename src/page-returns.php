<?php

/**
 * Template Name: Returns
 * Description: 返品・交換・キャンセルに関する案内ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="returns section">
  <div class="returns__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="returns__header">
          <h1 class="returns__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="returns__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="returns__content">
          <?php the_content(); ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
?>