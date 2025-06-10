export class HeaderMenu {
  constructor() {
    this.toggle = document.querySelector('.header__toggle');
    this.nav = document.querySelector('.header__nav');
    this.body = document.body;
    this.overlay = document.querySelector('.nav-overlay');
    this.isOpen = false;

    this.bindEvents();
  }

  bindEvents() {
    if (this.toggle && this.nav && this.overlay) {
      this.toggle.addEventListener('click', () => this.toggleMenu());

      // ナビ内リンククリックで閉じる
      const navLinks = this.nav.querySelectorAll('a');
      navLinks.forEach(link => {
        link.addEventListener('click', () => this.closeMenu());
      });

      // ナビ外クリック（オーバーレイクリック）で閉じる
      this.overlay.addEventListener('click', () => this.closeMenu());
    }
  }

  toggleMenu() {
    this.isOpen ? this.closeMenu() : this.openMenu();
  }

  openMenu() {
    this.isOpen = true;
    this.toggle.classList.add('is-open');
    this.nav.classList.add('is-open');
    this.overlay.classList.add('is-active');
    this.body.classList.add('no-scroll');
  }

  closeMenu() {
    this.isOpen = false;
    this.toggle.classList.remove('is-open');
    this.nav.classList.remove('is-open');
    this.overlay.classList.remove('is-active');
    this.body.classList.remove('no-scroll');
  }
}
//# sourceMappingURL=HeaderMenu.js.map
