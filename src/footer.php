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
      <nav class="footer__nav" aria-label="フッターナビゲーション">
        <ul class="footer__nav-list">
          <li class="footer__nav-item"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop</a></li>
          <li class="footer__nav-item"><a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a></li>
          <li class="footer__nav-item"><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></li>
          <li class="footer__nav-item"><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
          <li class="footer__nav-item"><a href="<?php echo esc_url(home_url('/access/')); ?>">Access</a></li>
        </ul>
      </nav>
    </div>

    <!-- 情報エリア -->
    <div class="footer__middle">
      <div class="footer__contact">
        <address>
          <p><strong>PRO SHOP WAVE</strong></p>
          <p>〒253-0083
            <a href="https://maps.app.goo.gl/2j8G8uKaDDwhdHmF9" target="_blank" rel="noopener noreferrer">
              神奈川県茅ヶ崎市西久保１５１８−１
            </a>
          </p>
          <p>TEL: <a href="tel:0467-88-1072">0467-88-1072</a></p>
          <p>Mail: <a href="mailto:info@proshop-wave.jp">info@proshop-wave.jp</a></p>
        </address>
      </div>
      <div class="footer__sns">
        <a href="https://www.instagram.com/proshopwave/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
        <a href="https://x.com/proshopwave" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
        <a href="https://www.facebook.com/proshopwave" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
      </div>
    </div>

    <!-- サポートメニュー -->
    <div class="footer__support">
      <a href="<?php echo esc_url(home_url('/guide/')); ?>">ご利用ガイド</a>
      <a href="<?php echo esc_url(home_url('/faq/')); ?>">よくある質問</a>
      <a href="<?php echo esc_url(home_url('/returns/')); ?>">返品・交換・キャンセルについて</a>
    </div>

    <!-- ポリシーなど -->
    <div class="footer__legal">
      <a href="<?php echo esc_url(home_url('/terms/')); ?>">利用規約</a>
      <a href="<?php echo esc_url(home_url('/privacy/')); ?>">プライバシーポリシー</a>
      <a href="<?php echo esc_url(home_url('/tokushoho/')); ?>">特商法表記</a>
    </div>

    <!-- コピーライト -->
    <div class="footer__copy">
      © <?php echo date('Y'); ?> PRO SHOP WAVE
    </div>

  </div>
</footer>
</div>

<?php wp_footer(); ?>
</body>

</html>