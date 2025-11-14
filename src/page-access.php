<?php

/**
 * Template Name: Access
 * Description: アクセスページテンプレート
 *
 * @package proshopwave
 */

get_header();
?>

<main id="primary" class="access section">
  <!-- <div class="access__inner l-container l-container--wide"> -->
  <div class="access__inner l-container">

    <?php
    // パンくず
    get_template_part('template-parts/breadcrumb/breadcrumb');
    ?>

    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>

        <header class="access__header">
          <h1 class="access__title section__title">
            <?php the_title(); ?>
          </h1>

          <?php if (has_excerpt()) : ?>
            <p class="access__lead">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="access__content">
          <?php
          // 固定ページ本文（アクセスの補足説明などをブロックエディタで管理）
          the_content();
          ?>
        </div>

      <?php endwhile; ?>
    <?php endif; ?>

    <?php
    // 店舗情報 + アクセスマップセクション
    get_template_part('template-parts/access/access-info');
    ?>

  </div>
</main>

<?php
get_footer();
