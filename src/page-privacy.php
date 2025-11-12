<?php

/**
 * Template Name: Privacy Policy
 * Description: プライバシーポリシーページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="privacy section">
  <div class="privacy__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="privacy__header">
          <h1 class="privacy__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="privacy__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="privacy__content">
          <?php
          // 固定ページ本文（プライバシーポリシーの内容をブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
