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
      ?>
        <article class="card-blog fadeup">
          <a href="<?php the_permalink(); ?>" class="card-blog__link">
            <picture class="card-blog__picture">
              <?php if (has_post_thumbnail()) : ?>
                <?php
                $thumb_id = get_post_thumbnail_id();
                $thumb_webp = wp_get_attachment_image_src($thumb_id, 'full')[0];
                ?>
                <source srcset="<?php echo esc_url($thumb_webp); ?>" type="image/webp">
                <?php the_post_thumbnail('full', ['class' => 'card-blog__image']); ?>
              <?php else : ?>
                <source srcset="<?php echo esc_url(get_theme_file_uri('images/default-thumbnail.webp')); ?>" type="image/webp">
                <img src="<?php echo esc_url(get_theme_file_uri('images/default-thumbnail.jpg')); ?>" alt="デフォルト画像" class="card-blog__image">
              <?php endif; ?>
            </picture>
            <div class="card-blog__content">
              <time class="card-blog__date" datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
              <h3 class="card-blog__title"><?php the_title(); ?></h3>
              <p class="card-blog__text"><?php echo wp_trim_words(get_the_excerpt(), 20, '…'); ?></p>
              <?php
              $post_tags = get_the_tags();
              if ($post_tags) :
                $tags = array_map(fn($tag) => '#' . esc_html($tag->name), $post_tags);
              ?>
                <div class="card-blog__tags"><?php echo implode(' ', $tags); ?></div>
              <?php endif; ?>
              <div class="card-blog__more">
                <span class="card-blog__more-link">Read More<i class="fas fa-arrow-right"></i></span>
              </div>
            </div>
          </a>
        </article>
      <?php
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