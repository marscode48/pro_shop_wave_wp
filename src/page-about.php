<?php

/**
 * Template Name: About
 * Description: PRO SHOP WAVE についての紹介ページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="about section">
  <div class="about__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="about__header">
          <h1 class="about__title section__title fadeup">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="about__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <?php if (has_post_thumbnail()) : ?>
          <div class="about__thumb fadeup">
            <?php
            // アバウトページ用のアイキャッチ画像（サムネイル）
            the_post_thumbnail('large', array(
              'class'   => 'about__thumb-image',
              'loading' => 'lazy',
            ));
            ?>
          </div>
        <?php endif; ?>

        <div class="about__content fadeup">
          <?php
          // 固定ページ本文（ショップ紹介・理念・沿革・スタッフ紹介などをブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</main>

<?php
get_footer();
