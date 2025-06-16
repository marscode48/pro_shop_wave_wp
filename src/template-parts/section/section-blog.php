<?php
/**
 * Template part for Blog section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--blog">
  <div class="section__inner">
    <h2 class="section__title fadeup">Blog</h2>
    <p class="section__text fadeup">ドリフトパーツやアパレルの<br class="sp-only">最新情報をピックアップ。</p>
    <div class="card-list card-list--blog">
      <?php
      $blog_query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
      ]);

      if ($blog_query->have_posts()) :
        while ($blog_query->have_posts()) : $blog_query->the_post();
          get_template_part('template-parts/card/card-blog');
        endwhile;
        wp_reset_postdata();
      endif;
      ?>
    </div>
    <div class="section__button-area faderight">
      <a href="/blog/" class="section__button">
        <span class="section__button-inner">View All</span>
      </a>
    </div>
  </div>
</section>