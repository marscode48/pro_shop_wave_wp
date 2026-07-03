// ---------------------------------
// modules/header-submenu-toggle.js
// ---------------------------------
// Shop配下のサブメニュー（Custom Parts/Apparel & Goods）の開閉制御
// - トグルボタンをクリックで親<li>に is-open を付与/除去
// - aria-expanded を true/false に更新
// - 別の場所をクリックでクローズ（PC向け）
// - SPでトグル非表示・常時表示でも安全に無害（btnが無ければ何もしない）

export class HeaderSubmenuToggle {
  constructor(options = {}) {
    this.containerSelector = options.containerSelector || '.header__nav-item--has-children';
    this.buttonSelector    = options.buttonSelector    || '.header__nav-toggle';
    this.openClass         = options.openClass         || 'is-open';
    this._onDocClick       = this._onDocClick.bind(this);
    this.init();
  }

  init() {
    this.items = Array.from(document.querySelectorAll(this.containerSelector));
    if (!this.items.length) return; // 何もなければ何もしない

    this.items.forEach(item => {
      const btn = item.querySelector(this.buttonSelector);
      if (!btn) return; // SPなどでトグル非表示のケース

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const willOpen = !item.classList.contains(this.openClass);
        this.closeAll();
        if (willOpen) {
          item.classList.add(this.openClass);
          btn.setAttribute('aria-expanded', 'true');
        } else {
          item.classList.remove(this.openClass);
          btn.setAttribute('aria-expanded', 'false');
        }
      });
    });

    // 外側クリックで閉じる（PC想定：SPではトグル非表示のため影響なし）
    document.addEventListener('click', this._onDocClick, true);
  }

  closeAll() {
    if (!this.items) return;
    this.items.forEach(item => {
      if (item.classList.contains(this.openClass)) {
        item.classList.remove(this.openClass);
        const btn = item.querySelector(this.buttonSelector);
        if (btn) btn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  _onDocClick(e) {
    const hit = e.target.closest(this.containerSelector);
    if (!hit) this.closeAll();
  }
}