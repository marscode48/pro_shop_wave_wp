<?php

/**
 * The template for displaying single blog posts
 *
 * @package PRO_SHOP_WAVE
 */
?>

<?php get_header(); ?>

<main class="single-blog section section--commerce-ui">
  <div class="single-blog__inner l-container l-container--wide">
    <?php get_template_part('template-parts/breadcrumb/breadcrumb'); ?>
    <div class="single-blog-layout">
      <div class="single-blog__body fadeup">
        <?php
        // ヘッダー（タイトル / 日付 / カテゴリ / サムネイル）
        get_template_part('template-parts/single/single-header');

        // コンテンツ本文
        get_template_part('template-parts/single/single-content');
        ?>

        <?php
        // シェア
        get_template_part('template-parts/single/single-share');

        // 次/前ナビ（同カテゴリ優先）
        get_template_part('template-parts/single/single-nav');

        // 関連記事（6件）
        get_template_part('template-parts/single/single-related');

        // 著者（プロフィールがあれば）
        get_template_part('template-parts/single/single-author');

        // フッターメタ（カテゴリ / タグ）
        get_template_part('template-parts/single/single-footer-meta');
        ?>
      </div>

      <?php
      // サイドバー（おすすめ / カテゴリ / アーカイブ）
      get_template_part('template-parts/blog/blog-sidebar');
      ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>