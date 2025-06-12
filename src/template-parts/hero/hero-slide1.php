<?php
/**
 * Template part for Hero Slide 1
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
        type="image/webp"
      >
      <source
        media="(min-width: 960px)"
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_pc.jpg'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_pc@2x.jpg'); ?> 2x
        "
      >
      <!-- SP用画像（WebP & JPG） -->
      <source
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_sp.webp'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_sp@2x.webp'); ?> 2x
        "
        type="image/webp"
      >
      <source
        srcset="
          <?php echo get_theme_file_uri('images/hero_slide_01_sp.jpg'); ?> 1x,
          <?php echo get_theme_file_uri('images/hero_slide_01_sp@2x.jpg'); ?> 2x
        "
      >
      <img src="<?php echo get_theme_file_uri('images/hero_slide_01_sp.jpg'); ?>" alt="ヒーロー画像1" class="hero__image" />
    </picture>
    <div class="hero__content">
      <h2 class="hero__heading-en">
        <span class="hero__text-marker">90’s Drift Legacy.</span>
        <br>
        <span class="hero__text-marker">Born in Kanagawa.</span>
        <br>
        <span class="hero__text-marker"><span class="hero__highlight">JDM</span> Forever.</span>
      </h2>
      <p class="hero__subheading-jp">90年代ストリートの系譜を受け継ぐ、<br class="sp-only">神奈川発・ドリフトチューンの頂点。</p>
    </div>
  </div>
</div>