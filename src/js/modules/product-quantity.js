export class ProductQuantity {
  constructor(selector = ".js-product-quantity") {
    this.selector = selector;
    this.handleClick = this.handleClick.bind(this);
    this.init();
  }

  init() {
    // カート更新後はWooCommerceが数量フォームのHTMLをAjaxで差し替えるため、
    // 個別の数量ボタンではなくdocumentでクリックを受け取る。
    document.addEventListener("click", this.handleClick);
  }

  handleClick(event) {
    const button = event.target.closest(
      ".js-quantity-increase, .js-quantity-decrease",
    );

    if (!button) return;

    const container = button.closest(this.selector);
    if (!container) return;

    const input = container.querySelector("input.qty");
    if (!input) return;

    const step = parseInt(input.getAttribute("step"), 10) || 1;
    const min = parseInt(input.getAttribute("min"), 10) || 1;
    const maxAttr = input.getAttribute("max");
    const max = maxAttr ? parseInt(maxAttr, 10) : Infinity;
    const currentVal = parseInt(input.value, 10) || 0;

    if (button.classList.contains("js-quantity-increase")) {
      if (currentVal >= max) return;
      input.value = currentVal + step;
    }

    if (button.classList.contains("js-quantity-decrease")) {
      if (currentVal <= min) return;
      input.value = currentVal - step;
    }

    input.dispatchEvent(new Event("change", { bubbles: true }));
  }
}
