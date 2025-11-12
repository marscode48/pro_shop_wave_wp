<?php

/**
 * Template Name: FAQ
 * Description: よくある質問ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="faq section">
  <div class="faq__inner l-container">

    <?php get_template_part('template-parts/breadcrumb/breadcrumb'); ?>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <header class="faq__header">
          <h1 class="faq__title section__title"><?php the_title(); ?></h1>

          <?php if (has_excerpt()) : ?>
            <p class="faq__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
          <?php endif; ?>
        </header>

        <div class="faq__content">
          <?php
          // 固定ページ本文（Q&Aの内容をブロックエディタで管理）
          the_content();
          ?>
        </div>

    <?php endwhile;
    endif; ?>

  </div>
</main>

<?php get_footer(); ?>