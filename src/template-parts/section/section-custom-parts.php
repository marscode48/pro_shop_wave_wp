<?php
/**
 * Template part for Custom Parts section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--categories section--custom-parts">
  <div class="section__flex">
    <div class="section__content">
      <h2 class="section__title fadeup">Custom Parts</h2>
      <p class="section__text fadeup">90’sストリートのスピリットを、<br class="sp-only">現代に甦らせるチューニングパーツ。</p>
      <button class="section__button faderight">
        <span class="section__button-inner">More</span>
      </button>
    </div>
    <a href="/custom-parts/" class="section__link">
      <picture class="section__picture fadeup">
        <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/section_parts_pc.webp'); ?>" type="image/webp">
        <img src="<?php echo get_theme_file_uri('images/section_parts_sp.jpg'); ?>" alt="カスタムパーツカテゴリ" class="section__image">
      </picture>
    </a>
  </div>
</section>