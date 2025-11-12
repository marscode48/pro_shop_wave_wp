export class ShareButtons {
  /**
   * ルート要素(single-blog__share)を受け取って初期化
   * @param {HTMLElement} root
   */
  constructor(root) {
    this.root = root;
    this.nativeBtn = this.root.querySelector(".share__icon--native");
    this.copyBtn = this.root.querySelector(".share__icon--copy");
  }

  /**
   * 初期化エントリ
   */
  init() {
    this.#setupNativeShare();
    this.#setupCopy();
  }

  /**
   * Web Share API (navigator.share) を使ったネイティブ共有
   * APIがサポートされていない環境ではボタン自体を隠す
   * @private
   */
  #setupNativeShare() {
    if (!this.nativeBtn) return;

    const title =
      this.nativeBtn.getAttribute("data-share-title") || document.title;
    const url =
      this.nativeBtn.getAttribute("data-share-url") || window.location.href;

    // API非対応ならボタンを非表示
    if (!navigator.share) {
      this.nativeBtn.style.display = "none";
      return;
    }

    this.nativeBtn.addEventListener("click", async () => {
      try {
        await navigator.share({ title, url });
      } catch (err) {
        // ユーザーがキャンセルした場合などは特に何もしない
        // console.warn('Share canceled or failed:', err);
      }
    });
  }

  /**
   * リンクコピー機能
   * Clipboard API が使えない場合は textarea を使ったフォールバック
   * @private
   */
  #setupCopy() {
    if (!this.copyBtn) return;

    const url =
      this.copyBtn.getAttribute("data-copy-url") || window.location.href;

    this.copyBtn.addEventListener("click", async () => {
      let copied = false;

      // Clipboard API がある場合
      if (navigator.clipboard && navigator.clipboard.writeText) {
        try {
          await navigator.clipboard.writeText(url);
          copied = true;
        } catch (e) {
          copied = false;
        }
      }

      // フォールバック (一時的に textarea を作成して選択→コピー)
      if (!copied) {
        const ta = document.createElement("textarea");
        ta.value = url;
        ta.setAttribute("readonly", "");
        ta.style.position = "fixed";
        ta.style.top = "-9999px";
        document.body.appendChild(ta);

        ta.select();
        try {
          document.execCommand("copy");
          copied = true;
        } catch (e) {
          copied = false;
        } finally {
          document.body.removeChild(ta);
        }
      }

      if (copied) {
        this.#flashCopied();
      }
    });
  }

  /**
   * 「Copied!」のフィードバックを一瞬だけ表示
   * .share__icon--copy の aria-label も一時的に変える
   * @private
   */
  #flashCopied() {
    if (!this.copyBtn) return;

    const originalAria = this.copyBtn.getAttribute("aria-label") || "";

    // .is-copied クラスを一時的に付与して視覚フィードバックを表示
    this.copyBtn.classList.add("is-copied");
    this.copyBtn.setAttribute("aria-label", "Copied!");

    window.setTimeout(() => {
      this.copyBtn.classList.remove("is-copied");
      this.copyBtn.setAttribute("aria-label", originalAria);
    }, 1500);
  }

  /**
   * ページ内に存在する .single-blog__share をすべて初期化します。
   * main.js から ShareButtons.bootAll() を1回呼び出すだけで、
   * ページ内すべてのシェアブロックが自動的に動作するようになります。
   *
   * 「全部初期化して動かすだけ」で良い場合は、
   * static bootAll() を利用するのが最適です。
   */
  static bootAll() {
    const roots = document.querySelectorAll(".single-blog__share");
    roots.forEach((root) => {
      const instance = new ShareButtons(root);
      instance.init();
    });
  }
}
