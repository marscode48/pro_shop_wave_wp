<?php

/**
 * Template part for Hero Slide 1
 *
 * @package PRO_SHOP_WAVE
 */
?>
<div class="swiper-slide">
  <div class="hero-slide">
    <picture class="slide-media">
      <!-- PC用画像（WebP & JPG） -->
      <source
        media="(min-width: 960px)"
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_pc.webp'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_pc@2x.webp'); ?> 2x
        "
        type="image/webp">
      <source
        media="(min-width: 960px)"
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_pc.jpg'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_pc@2x.jpg'); ?> 2x
        ">
      <!-- SP用画像（WebP & JPG） -->
      <source
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_sp.webp'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_sp@2x.webp'); ?> 2x
        "
        type="image/webp">
      <source
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_sp.jpg'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_sp@2x.jpg'); ?> 2x
        ">
      <img src="<?php echo get_theme_file_uri('images/hero_slide_01_sp.jpg'); ?>" alt="<?php echo esc_attr__('Hero Image 1', 'proshopwave'); ?>" class="hero__image" />
    </picture>
    <div class="hero__content">
      <h2 class="hero__heading-en">
        <span class="hero__text-marker"><?php echo esc_html__("90’s Drift Legacy.", 'proshopwave'); ?></span>
        <br>
        <span class="hero__text-marker"><?php echo esc_html__("Born in Yokohama.", 'proshopwave'); ?></span>
        <br>
        <span class="hero__text-marker"><?php echo wp_kses_post(__('<span class="hero__highlight">JDM</span> Forever.')); ?></span>
      </h2>
      <p class="hero__subheading-jp">
        <?php echo wp_kses_post(__('90年代ストリートの系譜を受け継ぐ、<br class="sp-only">横浜発・ドリフトチューンの頂点。', 'proshopwave')); ?>
      </p>
    </div>
  </div>
</div>