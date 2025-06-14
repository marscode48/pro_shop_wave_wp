<?php
/**
 * Template part for Apparel section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--categories section--apparel section--reverse">
  <div class="section__flex">
    <div class="section__content">
      <h2 class="section__title fadeup">Apparel</h2>
      <p class="section__text fadeup">90’sストリートカルチャーを纏う、<br class="sp-only">オリジナルJDMアパレル。</p>
      <button class="section__button faderight">More</button>
    </div>
    <a href="/apparel/" class="section__link">
      <picture class="section__picture fadeup">
        <source media="(min-width: 960px)" srcset="<?php echo get_theme_file_uri('images/section_apparel_pc.webp'); ?>" type="image/webp">
        <img src="<?php echo get_theme_file_uri('images/section_apparel_sp.jpg'); ?>" alt="アパレルカテゴリ" class="section__image">
      </picture>
    </a>
  </div>
</section>