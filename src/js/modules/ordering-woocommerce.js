export class OrderingWooCommerce {
  constructor() {
    this.ordering = document.querySelector(".woocommerce-ordering");
    this.select = this.ordering?.querySelector("select.orderby");
    if (!this.ordering || !this.select) return;

    this.#bindEvents();
  }

  // イベントをバインド
  #bindEvents() {
    // フォーカス/クリック時にクラス追加
    this.select.addEventListener("focus", () =>
      this.ordering.classList.add("is-active")
    );
    this.select.addEventListener("click", () =>
      this.ordering.classList.add("is-active")
    );

    // フォーカスが外れたら削除
    this.select.addEventListener("blur", () =>
      this.ordering.classList.remove("is-active")
    );
  }
}
