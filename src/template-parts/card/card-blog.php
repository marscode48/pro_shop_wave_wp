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
        <img src="<?php echo esc_url(get_theme_file_uri('images/default-thumbnail.jpg')); ?>" alt="<?php echo esc_attr__( 'Default Image', 'proshopwave' ); ?>" class="card-blog__image">
      <?php endif; ?>
    </picture>
    <div class="card-blog__content">
      <?php get_template_part('template-parts/meta/meta', 'date', ['class' => 'card-blog__date']); ?>
      <h3 class="card-blog__title"><?php the_title(); ?></h3>
      <p class="card-blog__text"><?php echo wp_trim_words(get_the_excerpt(), 35, '…'); ?></p>
    </div>
  </a>

  <div class="card-blog__meta">
    <?php
    $post_tags = get_the_tags();
    if ($post_tags) :
      $tags_html = array_map(function ($tag) {
        $url  = get_tag_link($tag->term_id);
        $name = '#' . $tag->name;
        return '<a class="card-blog__tag" href="' . esc_url($url) . '">' . esc_html($name) . '</a>';
      }, $post_tags);
    ?>
      <div class="card-blog__tags"><?php echo implode(' ', $tags_html); ?></div>
    <?php endif; ?>
    <div class="card-blog__more">
      <a href="<?php the_permalink(); ?>" class="card-blog__more-link">
        <?php echo esc_html__( 'Read More', 'proshopwave' ); ?><i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</article>