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
      <h2 class="section__title fadeup"><?php echo esc_html__('Apparel', 'proshopwave'); ?></h2>
      <p class="section__text fadeup">
        <?php echo wp_kses_post(__('90’sストリートカルチャーを纏う、<br class="sp-only">オリジナルJDMアパレル。', 'proshopwave')); ?>
      </p>
      <a href="/product-category/apparel/" class="section__button faderight">
        <?php echo esc_html__('More', 'proshopwave'); ?>
      </a>
    </div>
    <a href="/product-category/apparel/" class="section__link">
      <picture class="section__picture fadeup">
        <!-- PC: WebP + JPG -->
        <source
          media="(min-width: 960px)"
          srcset="<?php echo get_theme_file_uri('images/section-apparel-pc@2x.webp'); ?> 2x, <?php echo get_theme_file_uri('images/section-apparel-pc.webp'); ?> 1x"
          type="image/webp" />
        <source
          media="(min-width: 960px)"
          srcset="<?php echo get_theme_file_uri('images/section-apparel-pc@2x.jpg'); ?> 2x, <?php echo get_theme_file_uri('images/section-apparel-pc.jpg'); ?> 1x" />

        <!-- SP: WebP + JPG -->
        <source
          srcset="<?php echo get_theme_file_uri('images/section-apparel-sp@2x.webp'); ?> 2x, <?php echo get_theme_file_uri('images/section-apparel-sp.webp'); ?> 1x"
          type="image/webp" />
        <img
          src="<?php echo get_theme_file_uri('images/section-apparel-sp.jpg'); ?>"
          alt="<?php echo esc_attr__('アパレルカテゴリ', 'proshopwave'); ?>"
          class="section__image" />
      </picture>
    </a>
  </div>
</section>