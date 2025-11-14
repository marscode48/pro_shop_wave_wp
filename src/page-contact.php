<?php

/**
 * Template Name: Contact
 * Description: お問い合わせページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="contact section">
  <div class="contact__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="contact__header">
          <h1 class="contact__title section__title fadeup">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="contact__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="contact__content fadeup">
          <?php
          // 固定ページ本文（説明テキストなど）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

    <?php
    // お問い合わせフォーム（Contact Form 7）
    get_template_part('template-parts/contact/contact-form');
    ?>

  </div>
</main>

<?php
get_footer();
