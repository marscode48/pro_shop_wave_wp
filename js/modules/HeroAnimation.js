import { gsap } from 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/index.js';

export default class HeroAnimation {
  constructor() {
    this.init();
  }

  init() {
    gsap.from(".hero__text h2", {
      opacity: 0,
      y: 50,
      duration: 1,
      delay: 0.5,
      ease: "power3.out",
    });

    gsap.from(".hero__text p", {
      opacity: 0,
      y: 30,
      duration: 1,
      delay: 0.8,
      ease: "power3.out",
    });

    gsap.from(".hero__text a", {
      opacity: 0,
      scale: 0.9,
      duration: 0.8,
      delay: 1.2,
      ease: "back.out(1.7)",
    });
  }
}

//# sourceMappingURL=HeroAnimation.js.map
