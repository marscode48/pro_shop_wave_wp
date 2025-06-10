export class VivusLogo {
  constructor() {
    this.initVivus();
  }

  initVivus() {
    const configs = [
      {
        selector: '.vivus-logo-top',
        type: 'oneByOne',
        duration: 150,
        callback: () => {
          document.querySelector('.vivus-logo').classList.add('vivus-finished');
        }
      },
      {
        selector: '.vivus-logo-arrow',
        type: 'sync',
        duration: 150
      },
      {
        selector: '.vivus-logo-bottom',
        type: 'delayed',
        duration: 1000
      }
    ];

    configs.forEach(({ selector, type, duration, callback }) => {
      const element = document.querySelector(selector);
      if (element) {
        new Vivus(element, {
          type,
          duration,
          animTimingFunction: Vivus.EASE
        }, callback);
      }
    });
  }
}
//# sourceMappingURL=vivus-logo.js.map
