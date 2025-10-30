<?php

/**
 * Template part for Blog Card
 *
 * @package PRO_SHOP_WAVE
 */
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
      <?php get_template_part('template-parts/meta/meta', 'date', ['class' => 'card-blog__date']); ?>
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