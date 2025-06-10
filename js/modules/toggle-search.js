export class ToggleSearch {
  constructor(toggleSelector = '.header__search-toggle', formSelector = '.search-form-sp') {
    this.toggle = document.querySelector(toggleSelector);
    this.form = document.querySelector(formSelector);

    if (this.toggle && this.form) {
      this.bindEvents();
    }
  }

  bindEvents() {
    this.toggle.addEventListener('click', () => {
      this.form.classList.toggle('is-visible');
    });
  }
}
//# sourceMappingURL=toggle-search.js.map
