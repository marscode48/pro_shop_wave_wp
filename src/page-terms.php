<?php

/**
 * Template Name: Terms
 * Description: 利用規約ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="terms section">
  <div class="terms__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="terms__header">
          <h1 class="terms__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="terms__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="terms__content">
          <?php
          // 固定ページ本文（利用規約の内容をブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
