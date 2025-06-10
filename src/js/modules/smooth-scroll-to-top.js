export class SmoothScrollToTop {
  constructor() {
    this.bindEvents();
  }

  bindEvents() {
    document.querySelectorAll('.header__logo a, .footer__brand a').forEach(link => {
      link.addEventListener('click', event => {
        event.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    });
  }
}