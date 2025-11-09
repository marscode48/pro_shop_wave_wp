<?php

/**
 * Template Name: Tokushoho
 * Description: 特定商取引法に基づく表記ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="tokushoho section">
  <div class="tokushoho__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="tokushoho__header">
          <h1 class="tokushoho__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="tokushoho__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="tokushoho__content">
          <?php
          // 固定ページ本文（特定商取引法に基づく表記の内容をブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
