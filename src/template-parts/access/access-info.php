<?php

/**
 * Template part: Access info
 *
 * 店舗情報とアクセスマップ・アクセス案内を表示するコンポーネント。
 * レイアウトやスタイルは sass/pages/_access.scss で調整予定。
 *
 * @package proshopwave
 */
?>

<section class="access-info fadeup">
  <div class="access-info__inner">

    <div class="access-info__grid">
      <div class="access-info__summary">

        <h2 class="access-info__heading">
          <?php esc_html_e('店舗情報', 'proshopwave'); ?>
        </h2>

        <dl class="access-info__list">

          <div class="access-info__item access-info__item--name">
            <dt class="access-info__term">
              <?php esc_html_e('ショップ名', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              PRO SHOP WAVE
            </dd>
          </div>

          <div class="access-info__item access-info__item--address">
            <dt class="access-info__term">
              <?php esc_html_e('住所', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              〒253-0083 神奈川県茅ヶ崎市西久保１５１８−１<br>
              <a href="https://maps.app.goo.gl/2j8G8uKaDDwhdHmF9" target="_blank" rel="noopener" class="access-info__map-link">
                <?php esc_html_e('Google Mapで見る', 'proshopwave'); ?>
              </a>
            </dd>
          </div>

          <div class="access-info__item access-info__item--tel">
            <dt class="access-info__term">
              <?php esc_html_e('TEL', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              <a href="tel:0467-88-1072" class="access-info__tel-link">
                0467-88-1072
              </a>
            </dd>
          </div>

          <div class="access-info__item access-info__item--hours">
            <dt class="access-info__term">
              <?php esc_html_e('営業時間', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              10:00〜20:00
            </dd>
          </div>

          <div class="access-info__item access-info__item--holiday">
            <dt class="access-info__term">
              <?php esc_html_e('定休日', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              <?php esc_html_e('不定休', 'proshopwave'); ?>
            </dd>
          </div>

          <div class="access-info__item access-info__item--parking">
            <dt class="access-info__term">
              <?php esc_html_e('駐車場', 'proshopwave'); ?>
            </dt>
            <dd class="access-info__desc">
              <?php esc_html_e('店舗前に数台分の専用駐車場あり。ローダウン車両でお越しの際はご注意ください。', 'proshopwave'); ?>
            </dd>
          </div>

        </dl>
      </div>

      <div class="access-info__map">
        <h2 class="access-info__heading access-info__heading--map">
          <?php esc_html_e('アクセスマップ', 'proshopwave'); ?>
        </h2>

        <div class="access-info__map-frame">
          <?php echo '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d813.553993306256!2d139.3965851!3d35.3502861!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x601852fcc006114b%3A0x8699501f38edaf58!2z77yI5pyJ77yJ6JOu5rK85ZWG5LqL!5e0!3m2!1sja!2sjp!4v1762501631266!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
          ?>
        </div>
      </div>
    </div>

    <section class="access-info__section access-info__section--car">
      <h3 class="access-info__section-title">
        <?php esc_html_e('お車でお越しの方', 'proshopwave'); ?>
      </h3>
      <p class="access-info__text">
        <?php esc_html_e('新湘南バイパス/圏央道 茅ケ崎中央IC より約3分。県道45号線沿い、茅ケ崎中央インダー交差点すぐそばです。', 'proshopwave'); ?>
      </p>
    </section>

    <section class="access-info__section access-info__section--train">
      <h3 class="access-info__section-title">
        <?php esc_html_e('電車でお越しの方', 'proshopwave'); ?>
      </h3>
      <p class="access-info__text">
        <?php esc_html_e('最寄り駅：相模線 香川駅 下車、徒歩約12分です。', 'proshopwave'); ?>
      </p>
    </section>

    <div class="access-info__cta faderight">
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="access-info__cta-link">
        <?php esc_html_e('お問い合わせ', 'proshopwave'); ?>
      </a>
    </div>

  </div>
</section>