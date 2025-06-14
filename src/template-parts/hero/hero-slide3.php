<?php
/**
 * Template part for Hero Slide 3
 *
 * @package PRO_SHOP_WAVE
 */
?>
<div class="swiper-slide">
  <div class="hero-slide">
    <picture class="slide-media">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero_slide_03_pc.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_03_pc@2x.webp'); ?> 2x" type="image/webp">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero_slide_03_pc.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_03_pc@2x.jpg'); ?> 2x">
      <source srcset="<?php echo get_theme_file_uri('images/hero_slide_03_sp.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_03_sp@2x.webp'); ?> 2x" type="image/webp">
      <source srcset="<?php echo get_theme_file_uri('images/hero_slide_03_sp.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_03_sp@2x.jpg'); ?> 2x">
      <img src="<?php echo get_theme_file_uri('images/hero_slide_03_sp.jpg'); ?>" alt="ヒーロー画像3" class="hero__image" />
    </picture>
    <div class="hero__content">
      <h2 class="hero__heading-en">
        <span class="hero__text-marker">Born from Kanagawa, </span><br>
        <span class="hero__text-marker">Built for the <span class="hero__highlight">World.</span></span><br>
        <span class="hero__text-marker">Drift Culture Lives On.</span>
      </h2>
      <p class="hero__subheading-jp">本物のドリフトカルチャーを、<br class="sp-only">神奈川から世界へ。</p>
    </div>
  </div>
</div>