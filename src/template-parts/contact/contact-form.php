<?php

/**
 * Template part: Contact form
 *
 * @package proshopwave
 */

// Contact Form 7 ショートコード
$contact_form_shortcode = '[contact-form-7 id="de3be12" title="お問い合わせフォーム"]';
?>

<section class="contact-form">
  <div class="contact-form__inner">
    <header class="contact-form__header">
      <h2 class="contact-form__title">
        <?php esc_html_e('お問い合わせフォーム', 'proshopwave'); ?>
      </h2>
      <p class="contact-form__description">
        <?php esc_html_e('必要事項をご入力のうえ送信してください。内容を確認後、担当者よりご連絡いたします。', 'proshopwave'); ?>
      </p>
    </header>

    <div class="contact-form__body">
      <?php echo do_shortcode($contact_form_shortcode); ?>
    </div>

    <p class="contact-form__note">
      <?php esc_html_e('※ お問い合わせ内容によってはご回答までお時間をいただく場合がございます。', 'proshopwave'); ?>
    </p>
  </div>
</section>