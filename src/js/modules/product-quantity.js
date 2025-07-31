export class ProductQuantity {
  constructor(selector = '.product-quantity') {
    this.selector = selector;
    this.init();
  }

  init() {
    const quantityContainers = document.querySelectorAll(this.selector);

    quantityContainers.forEach(container => {
      const input = container.querySelector('input.qty');
      const btnIncrease = container.querySelector('.js-quantity-increase');
      const btnDecrease = container.querySelector('.js-quantity-decrease');

      if (!input || !btnIncrease || !btnDecrease) return;

      const step = parseInt(input.getAttribute('step')) || 1;
      const min = parseInt(input.getAttribute('min')) || 1;
      const maxAttr = input.getAttribute('max');
      const max = maxAttr ? parseInt(maxAttr) : Infinity;

      btnIncrease.addEventListener('click', () => {
        let currentVal = parseInt(input.value) || 0;
        if (currentVal < max) {
          input.value = currentVal + step;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });

      btnDecrease.addEventListener('click', () => {
        let currentVal = parseInt(input.value) || 0;
        if (currentVal > min) {
          input.value = currentVal - step;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    });
  }
}