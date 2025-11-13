export class SmoothScrollToTop {
  constructor() {
    this.buttonSelector = ".back-to-top";
    this.buttons = document.querySelectorAll(this.buttonSelector);
    this.threshold = 200;

    if (!this.buttons.length) return;

    this.bindEvents();
    this.handleScroll();
  }

  bindEvents() {
    this.buttons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        this.scrollToTop();
      });
    });
    window.addEventListener("scroll", this.handleScroll.bind(this), { passive: true });
  }

  handleScroll() {
    const shouldShow = window.scrollY > this.threshold;
    this.buttons.forEach((button) => {
      button.classList.toggle("is-visible", shouldShow);
    });
  }

  scrollToTop() {
    // 対応状況チェック（古いブラウザ対策）
    if ("scrollBehavior" in document.documentElement.style) {
      window.scrollTo({ top: 0, behavior: "smooth" });
    } else {
      // フォールバック：スムーススクロール風アニメーション
      let current = window.scrollY;
      const step = () => {
        if (current > 0) {
          current -= current / 8; // 減速スクロール
          window.scrollTo(0, current);
          requestAnimationFrame(step);
        }
      };
      requestAnimationFrame(step);
    }
  }
}
