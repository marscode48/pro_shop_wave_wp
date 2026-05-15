<?php

/**
 * The template for displaying the footer
 *
 * @package PRO_SHOP_WAVE
 */
?>
<!-- Footer -->
<footer class="footer">
  <div class="footer__inner">

    <!-- ナビゲーション -->
    <div class="footer__top">
      <div class="footer__brand">
        <a href="<?php echo esc_url(home_url('/')); ?>">
          <!-- Vivusアニメーション -->
          <?php get_template_part('template-parts/footer/logo-svg'); ?>
        </a>
      </div>
      <nav class="footer__nav" aria-label="<?php echo esc_attr__('フッターナビゲーション', 'proshopwave'); ?>">
        <ul class="footer__nav-list">
          <li class="footer__nav-item">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
              <?php esc_html_e('Shop', 'proshopwave'); ?>
            </a>
          </li>
          <li class="footer__nav-item">
            <a href="<?php echo esc_url(home_url('/blog/')); ?>">
              <?php esc_html_e('Blog', 'proshopwave'); ?>
            </a>
          </li>
          <li class="footer__nav-item">
            <a href="<?php echo esc_url(home_url('/about/')); ?>">
              <?php esc_html_e('About', 'proshopwave'); ?>
            </a>
          </li>
          <li class="footer__nav-item">
            <a href="<?php echo esc_url(home_url('/contact/')); ?>">
              <?php esc_html_e('Contact', 'proshopwave'); ?>
            </a>
          </li>
          <li class="footer__nav-item">
            <a href="<?php echo esc_url(home_url('/access/')); ?>">
              <?php esc_html_e('Access', 'proshopwave'); ?>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- 情報エリア -->
    <div class="footer__middle">
      <div class="footer__contact">
        <address>
          <p>
            <strong><?php esc_html_e('PRO SHOP WAVE', 'proshopwave'); ?></strong>
          </p>
          <p>
            <a href="https://maps.app.goo.gl/2j8G8uKaDDwhdHmF9" target="_blank" rel="noopener noreferrer">
              <?php esc_html_e('神奈川県茅ヶ崎市西久保１５１８−１', 'proshopwave'); ?>
            </a>
          </p>
          <p>
            <?php esc_html_e('TEL:', 'proshopwave'); ?>
            <a href="tel:0467-88-1072">0467-88-1072</a>
          </p>
          <p>
            <?php esc_html_e('Mail:', 'proshopwave'); ?>
            <a href="mailto:info@ps-wave.com">info@ps-wave.com</a>
          </p>
        </address>
      </div>
      <div class="footer__sns">
        <a href="https://www.instagram.com/proshopwave/" aria-label="<?php echo esc_attr__('Instagram', 'proshopwave'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="https://x.com/proshopwave" aria-label="<?php echo esc_attr__('X (Twitter)', 'proshopwave'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-x-twitter"></i>
        </a>
        <a href="https://www.facebook.com/proshopwave" aria-label="<?php echo esc_attr__('Facebook', 'proshopwave'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-facebook-f"></i>
        </a>
      </div>
    </div>

    <!-- サポートメニュー -->
    <div class="footer__support">
      <a href="<?php echo esc_url(home_url('/guide/')); ?>">
        <?php esc_html_e('ご利用ガイド', 'proshopwave'); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/faq/')); ?>">
        <?php esc_html_e('よくある質問', 'proshopwave'); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/returns/')); ?>">
        <?php esc_html_e('返品・交換・キャンセルについて', 'proshopwave'); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/shipping/')); ?>">
        <?php esc_html_e('配送・送料について', 'proshopwave'); ?>
      </a>
    </div>

    <!-- ポリシーなど -->
    <div class="footer__legal">
      <a href="<?php echo esc_url(home_url('/terms/')); ?>">
        <?php esc_html_e('利用規約', 'proshopwave'); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/privacy/')); ?>">
        <?php esc_html_e('プライバシーポリシー', 'proshopwave'); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/tokushoho/')); ?>">
        <?php esc_html_e('特商法表記', 'proshopwave'); ?>
      </a>
    </div>

    <!-- コピーライト -->
    <div class="footer__copy">
      <?php
      printf(
        /* translators: %s: year */
        esc_html__('© %s PRO SHOP WAVE', 'proshopwave'),
        esc_html(date('Y'))
      );
      ?>
    </div>

  </div>
</footer>
</div>

<!-- ページトップへ戻るボタン（固定UI） -->
<a href="#top" class="back-to-top" aria-label="<?php echo esc_attr__('ページトップへ戻る', 'proshopwave'); ?>">
  <i class="fas fa-chevron-up"></i>
</a>

<?php wp_footer(); ?>
</body>

</html>