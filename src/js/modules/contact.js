/**
 * Contact Form Controller
 * プライバシー同意チェックボックスがオンになるまで
 * 送信ボタンを無効化 & トーンを落とす制御を行うクラス。
 */
export class ContactFormController {
  constructor(root = document) {
    this.root = root;

    /** @type {HTMLInputElement|null} */
    this.privacyCheckbox = null;
    /** @type {HTMLInputElement|null} */
    this.submitButton = null;

    // Z初期化
    this.init();
  }

  /**
   * 初期化処理
   * 対象となるフォーム要素を取得し、イベントをバインドする
   */
  init() {
    // Contactページ内の Contact Form 7 フォームを対象にする
    const formWrapper = this.root.querySelector(".contact-form .wpcf7");
    if (!formWrapper) return;

    this.privacyCheckbox = formWrapper.querySelector(
      'input.privacy-agree[type="checkbox"], .privacy-agree input[type="checkbox"]'
    );
    this.submitButton = formWrapper.querySelector(".contact-form__submit");

    if (!this.privacyCheckbox || !this.submitButton) {
      return;
    }

    // 初期状態を反映
    this.updateSubmitState();

    // チェック状態の変化を監視
    this.bindEvents();
  }

  /**
   * イベントバインド
   */
  bindEvents() {
    this.privacyCheckbox.addEventListener("change", () => {
      this.updateSubmitState();
    });
  }

  /**
   * 同意チェック状態に応じて送信ボタンの状態を更新
   */
  updateSubmitState() {
    const isChecked = this.privacyCheckbox.checked;

    if (isChecked) {
      this.enableSubmit();
    } else {
      this.disableSubmit();
    }
  }

  /**
   * 送信ボタンを有効化
   */
  enableSubmit() {
    this.submitButton.disabled = false;
    this.submitButton.classList.remove("is-disabled");
    this.submitButton.setAttribute("aria-disabled", "false");
  }

  /**
   * 送信ボタンを無効化
   */
  disableSubmit() {
    this.submitButton.disabled = true;
    this.submitButton.classList.add("is-disabled");
    this.submitButton.setAttribute("aria-disabled", "true");
  }
}
