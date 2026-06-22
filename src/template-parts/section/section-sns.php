<?php

/**
 * Template part for SNS section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--sns">
  <h2 class="section__title fadeup"><?php echo esc_html__('Follow Our Drift Style', 'proshopwave'); ?></h2>
  <p class="section__text fadeup">
    <?php echo wp_kses_post(__("90'sストリートチューンのリアルを、<br class=\"sp-only\">Instagramで。", 'proshopwave')); ?>
  </p>
  <div class="sns__widget fadeup">
    <script src="https://snapwidget.com/js/snapwidget.js"></script>
    <iframe
      src="https://snapwidget.com/embed/1125741"
      class="snapwidget-widget"
      allowtransparency="true"
      frameborder="0"
      scrolling="no"
      style="border:none; overflow:hidden; width:100%;"
      title="Instagram feed">
    </iframe>
  </div>
</section>