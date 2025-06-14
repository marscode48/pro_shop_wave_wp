<?php
/**
 * Template part for displaying the Hero section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="hero swiper hero-swiper">
  <div class="swiper-wrapper">
    <?php get_template_part('template-parts/hero/hero', 'slide1'); ?>
    <?php get_template_part('template-parts/hero/hero', 'slide2'); ?>
    <?php get_template_part('template-parts/hero/hero', 'slide3'); ?>
  </div>
  <div class="scroll-indicator">
    <span class="scroll-indicator__text">Scroll</span>
    <span class="scroll-indicator__arrow"></span>
  </div>
</section>