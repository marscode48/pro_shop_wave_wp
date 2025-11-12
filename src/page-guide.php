<?php

/**
 * Template Name: Guide
 * Description: PRO SHOP WAVE ご利用ガイドページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="guide section">
  <div class="guide__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="guide__header">
          <h1 class="guide__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="guide__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="guide__content">
          <?php
          // 固定ページ本文（ご利用ガイドの内容をブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
