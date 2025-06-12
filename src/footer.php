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
              <li class="footer__nav-item"><a href="#">Parts</a></li>
              <li class="footer__nav-item"><a href="#">Apparel</a></li>
              <li class="footer__nav-item"><a href="#">Blog</a></li>
              <li class="footer__nav-item"><a href="#">Contact</a></li>
              <li class="footer__nav-item"><a href="#">Access</a></li>
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
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>

        <!-- ポリシーなど -->
        <div class="footer__legal">
          <a href="#">利用規約</a>
          <a href="#">プライバシーポリシー</a>
          <a href="#">特商法表記</a>
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