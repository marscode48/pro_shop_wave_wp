<?php
/**
 * Template part for Hero Slide 2
 */
?>
<div class="swiper-slide">
  <div class="hero-slide">
    <picture class="slide-media">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero_slide_02_pc.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_02_pc@2x.webp'); ?> 2x" type="image/webp">
      <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/hero_slide_02_pc.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_02_pc@2x.jpg'); ?> 2x">
      <source srcset="<?php echo get_theme_file_uri('images/hero_slide_02_sp.webp'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_02_sp@2x.webp'); ?> 2x" type="image/webp">
      <source srcset="<?php echo get_theme_file_uri('images/hero_slide_02_sp.jpg'); ?> 1x, <?php echo get_theme_file_uri('images/hero_slide_02_sp@2x.jpg'); ?> 2x">
      <img src="<?php echo get_theme_file_uri('images/hero_slide_02_sp.jpg'); ?>" alt="ヒーロー画像2" class="hero__image" />
    </picture>
    <div class="hero__content">
      <h2 class="hero__heading-en">
        <span class="hero__text-marker">Slide Hard.</span><br>
        <span class="hero__text-marker">Style Bold.</span><br>
        <span class="hero__text-marker"><span class="hero__highlight">WAVE</span> is Drift Culture.</span>
      </h2>
      <p class="hero__subheading-jp">攻めの走り、攻めのスタイル。<br class="sp-only">― それがWAVE。</p>
    </div>
  </div>
</div>