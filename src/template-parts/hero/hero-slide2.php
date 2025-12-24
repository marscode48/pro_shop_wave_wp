<?php

/**
 * Template part for Hero Slide 2
 *
 * @package PRO_SHOP_WAVE
 */
?>
<div class="swiper-slide">
  <div class="hero-slide">
    <picture class="slide-media">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero-slide-02-pc.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero-slide-02-pc@2x.webp'); ?> 2x" type="image/webp">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero-slide-02-pc.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero-slide-02-pc@2x.jpg'); ?> 2x">
      <source srcset="<?php echo get_theme_file_uri('images/hero-slide-02-sp.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero-slide-02-sp@2x.webp'); ?> 2x" type="image/webp">
      <source srcset="<?php echo get_theme_file_uri('images/hero-slide-02-sp.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero-slide-02-sp@2x.jpg'); ?> 2x">
      <img src="<?php echo get_theme_file_uri('images/hero-slide-02-sp.jpg'); ?>" alt="<?php echo esc_attr__('Hero image 2', 'proshopwave'); ?>" class="hero__image" />
    </picture>
    <div class="hero__content">
      <h2 class="hero__heading">
        <span class="hero__text-marker"><?php echo esc_html__('Slide Hard.', 'proshopwave'); ?></span><br>
        <span class="hero__text-marker"><?php echo esc_html__('Style Bold.', 'proshopwave'); ?></span><br>
        <span class="hero__text-marker"><?php echo wp_kses_post(__('<span class="hero__highlight">WAVE</span> is Drift Culture.')); ?></span>
      </h2>
      <p class="hero__subheading">
        <?php echo wp_kses_post(__('攻めの走り、攻めのスタイル。<br class="sp-only">― それがWAVE。', 'proshopwave')); ?>
      </p>
    </div>
  </div>
</div>