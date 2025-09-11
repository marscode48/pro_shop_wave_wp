// ========================
// SubmitButtonDecorator
// フォーム送信ボタンに section__button クラスを追加する
// ========================
export class SubmitButtonDecorator {
  constructor() {
    this.selectors = [
      'button.single_add_to_cart_button',
      '.comment-form .submit'
      // 必要に応じて追加
      // 'button[type="submit"]',
      // 'input[type="submit"]',
      // 'button.submit',
      // 'input.submit'
    ];
    this.buttons = document.querySelectorAll(this.selectors.join(','));
    this.decorate();
  }

  decorate() {
    this.buttons.forEach((btn) => {
      btn.classList.add("section__button");
    });
  }
}
