<?php

/**
 * Single: Share block
 *
 * 出力対象:
 * - Web Share API ボタン（対応ブラウザのみ）
 * - X(Twitter) / Facebook 共有リンク
 * - リンクコピー（Clipboard API）
 *
 * マークアップは `src/sass/pages/_single-blog.scss` の
 * `.single-blog__share` スタイルに対応しています。
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

// X(Twitter) intent・Facebook sharer
$x_share_url         = "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";
$facebook_share_url  = "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";
$line_share_url      = "https://social-plugins.line.me/lineit/share?url={$encoded_url}";
?>
<div class="single-blog__share" role="region" aria-labelledby="single-share-title">
  <h2 id="single-share-title" class="single-blog__share-title">
    <?php echo esc_html__('この記事をシェア', 'proshopwave'); ?>
  </h2>

  <div class="single-blog__share-actions">
    <!-- Web Share API（対応ブラウザのみ） -->
    <button
      type="button"
      class="share__native"
      data-share-title="<?php echo esc_attr($title); ?>"
      data-share-url="<?php echo esc_url($permalink); ?>"
      aria-label="<?php echo esc_attr__('ネイティブ共有メニューを開く', 'proshopwave'); ?>">
      <?php echo esc_html__('今すぐシェア', 'proshopwave'); ?>
    </button>

    <!-- X (Twitter) -->
    <a
      class="share__link share__link--x"
      href="<?php echo esc_url($x_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('X (Twitter) で共有', 'proshopwave'); ?>">
      <?php echo esc_html__('X で共有', 'proshopwave'); ?>
    </a>

    <!-- Facebook -->
    <a
      class="share__link share__link--facebook"
      href="<?php echo esc_url($facebook_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('Facebook で共有', 'proshopwave'); ?>">
      <?php echo esc_html__('Facebook で共有', 'proshopwave'); ?>
    </a>

    <!-- LINE -->
    <a
      class="share__link share__link--line"
      href="<?php echo esc_url($line_share_url); ?>"
      target="_blank"
      rel="noopener nofollow"
      aria-label="<?php echo esc_attr__('LINE で共有', 'proshopwave'); ?>">
      <?php echo esc_html__('LINE で共有', 'proshopwave'); ?>
    </a>


    <!-- URL コピー -->
    <button
      type="button"
      class="share__link share__link--copy"
      data-copy-url="<?php echo esc_url($permalink); ?>"
      aria-label="<?php echo esc_attr__('記事URLをコピー', 'proshopwave'); ?>">
      <?php echo esc_html__('リンクをコピー', 'proshopwave'); ?>
    </button>
  </div>
</div>

<script>
  // 単一記事のシェアUI（テンプレ内限定）
  (() => {
    const shareBtn = document.currentScript?.previousElementSibling?.querySelector?.('.share__native');
    const copyBtn = document.currentScript?.previousElementSibling?.querySelector?.('.share__link--copy');

    // ネイティブ共有（対応していない環境ではボタンを隠す）
    if (shareBtn) {
      if (!('share' in navigator)) {
        shareBtn.style.display = 'none';
      } else {
        shareBtn.addEventListener('click', () => {
          const title = shareBtn.getAttribute('data-share-title') || document.title;
          const url = shareBtn.getAttribute('data-share-url') || location.href;
          navigator.share({
            title,
            url
          }).catch(() => {
            /* キャンセル時は何もしない */ });
        });
      }
    }

    // リンクコピー（Clipboard API）
    if (copyBtn) {
      copyBtn.addEventListener('click', async () => {
        const url = copyBtn.getAttribute('data-copy-url') || location.href;
        try {
          await navigator.clipboard.writeText(url);
          copyBtn.classList.add('is-copied');
          copyBtn.textContent = '<?php echo esc_js(__('コピーしました', 'proshopwave')); ?>';
          setTimeout(() => {
            copyBtn.classList.remove('is-copied');
            copyBtn.textContent = '<?php echo esc_js(__('リンクをコピー', 'proshopwave')); ?>';
          }, 1600);
        } catch (_) {
          // フォールバック（非対応環境）
          const textArea = document.createElement('textarea');
          textArea.value = url;
          textArea.setAttribute('readonly', '');
          textArea.style.position = 'absolute';
          textArea.style.left = '-9999px';
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
          copyBtn.classList.add('is-copied');
          copyBtn.textContent = '<?php echo esc_js(__('コピーしました', 'proshopwave')); ?>';
          setTimeout(() => {
            copyBtn.classList.remove('is-copied');
            copyBtn.textContent = '<?php echo esc_js(__('リンクをコピー', 'proshopwave')); ?>';
          }, 1600);
        }
      });
    }
  })();
</script>