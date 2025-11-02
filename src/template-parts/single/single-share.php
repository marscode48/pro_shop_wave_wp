<?php

/**
 * Single: Share block (icon version)
 *
 * 出力対象:
 * - X(Twitter) / Facebook / LINE / コピー のアイコンリンク
 * - Web Share API ボタン（対応ブラウザのみ）
 *
 * @package PRO_SHOP_WAVE
 */

if (! defined('ABSPATH')) {
  exit;
}

$permalink = get_permalink();
$title     = get_the_title();

// 共有用 URL を組み立て（UTF-8でエンコード）
$encoded_url   = rawurlencode($permalink);
$encoded_title = rawurlencode($title);

// 各SNS共有URL
$x_share_url        = "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";
$facebook_share_url = "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";
$line_share_url     = "https://social-plugins.line.me/lineit/share?url={$encoded_url}";
?>

<div class="single-blog__share" role="region" aria-labelledby="single-share-title">
  <h2 id="single-share-title" class="single-blog__share-title">
    <?php echo esc_html__('＼ この記事をシェア ／', 'proshopwave'); ?>
  </h2>

  <div class="single-blog__share-actions">
    <!-- X (Twitter) -->
    <a
      class="share__icon share__icon--x"
      href="<?php echo esc_url($x_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('Share on X (Twitter)', 'proshopwave'); ?>">
      <i class="fab fa-x-twitter" aria-hidden="true"></i>
    </a>

    <!-- Facebook -->
    <a
      class="share__icon share__icon--facebook"
      href="<?php echo esc_url($facebook_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('Share on Facebook', 'proshopwave'); ?>">
      <i class="fab fa-facebook-f" aria-hidden="true"></i>
    </a>

    <!-- LINE -->
    <a
      class="share__icon share__icon--line"
      href="<?php echo esc_url($line_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('Share on LINE', 'proshopwave'); ?>">
      <i class="fab fa-line" aria-hidden="true"></i>
    </a>

    <!-- コピー -->
    <button
      type="button"
      class="share__icon share__icon--copy"
      data-copy-url="<?php echo esc_url($permalink); ?>"
      aria-label="<?php echo esc_attr__('Copy article URL', 'proshopwave'); ?>">
      <i class="fas fa-link" aria-hidden="true"></i>
    </button>

    <!-- Web Share API（対応ブラウザのみ） -->
    <button
      type="button"
      class="share__icon share__icon--native"
      data-share-title="<?php echo esc_attr($title); ?>"
      data-share-url="<?php echo esc_url($permalink); ?>"
      aria-label="<?php echo esc_attr__('Open native share menu', 'proshopwave'); ?>">
      <i class="fas fa-share-alt" aria-hidden="true"></i>
    </button>
  </div>
</div>

<script>
  (() => {
    const container = document.currentScript?.previousElementSibling;
    const copyBtn = container?.querySelector('.share__icon--copy');
    const nativeBtn = container?.querySelector('.share__icon--native');

    // ネイティブ共有ボタン制御
    if (nativeBtn) {
      if (!('share' in navigator)) {
        nativeBtn.style.display = 'none';
      } else {
        nativeBtn.addEventListener('click', () => {
          navigator.share({
            title: nativeBtn.getAttribute('data-share-title'),
            url: nativeBtn.getAttribute('data-share-url')
          }).catch(() => {});
        });
      }
    }

    // コピー機能
    if (copyBtn) {
      copyBtn.addEventListener('click', async () => {
        const url = copyBtn.getAttribute('data-copy-url');
        try {
          await navigator.clipboard.writeText(url);
          copyBtn.classList.add('is-copied');
          setTimeout(() => copyBtn.classList.remove('is-copied'), 1600);
        } catch (_) {
          const t = document.createElement('textarea');
          t.value = url;
          document.body.appendChild(t);
          t.select();
          document.execCommand('copy');
          document.body.removeChild(t);
          copyBtn.classList.add('is-copied');
          setTimeout(() => copyBtn.classList.remove('is-copied'), 1600);
        }
      });
    }
  })();
</script>