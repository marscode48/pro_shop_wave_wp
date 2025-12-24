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
      <?php /* translators: Section title */ ?>
      <h2 class="section__title fadeup"><?php echo esc_html__('Custom Parts', 'proshopwave'); ?></h2>
      <p class="section__text fadeup"><?php echo wp_kses_post(__('90’sストリートのスピリットを、<br class="sp-only">現代に甦らせるチューニングパーツ。', 'proshopwave')); ?></p>
      <a href="/product-category/parts/" class="section__button faderight">
        <span class="section__button-inner"><?php echo esc_html__('More', 'proshopwave'); ?></span>
      </a>
    </div>
    <a href="/product-category/parts/" class="section__link">
      <picture class="section__picture fadeup">
        <!-- PC: WebP + JPG -->
        <source
          media="(min-width: 960px)"
          srcset="
            <?php echo get_theme_file_uri('images/section-parts-pc@2x.webp'); ?> 2x,
            <?php echo get_theme_file_uri('images/section-parts-pc.webp'); ?> 1x
          "
          type="image/webp" />
        <source
          media="(min-width: 960px)"
          srcset="
            <?php echo get_theme_file_uri('images/section-parts-pc@2x.jpg'); ?> 2x,
            <?php echo get_theme_file_uri('images/section-parts-pc.jpg'); ?> 1x
          " />
        <!-- SP: WebP + JPG -->
        <source
          srcset="
            <?php echo get_theme_file_uri('images/section-parts-sp@2x.webp'); ?> 2x,
            <?php echo get_theme_file_uri('images/section-parts-sp.webp'); ?> 1x
          "
          type="image/webp" />
        <img
          src="<?php echo get_theme_file_uri('images/section-parts-sp.jpg'); ?>"
          alt="<?php echo esc_attr__('カスタムパーツカテゴリ', 'proshopwave'); ?>"
          class="section__image" />
      </picture>
    </a>
  </div>
</section>