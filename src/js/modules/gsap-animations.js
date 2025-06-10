import { gsap } from "https://cdn.jsdelivr.net/npm/gsap@3.12.5/index.js";
import { ScrollTrigger } from "https://cdn.jsdelivr.net/npm/gsap@3.12.5/ScrollTrigger.js";

gsap.registerPlugin(ScrollTrigger);

export class GsapAnimations {
  constructor(options = {}) {
    // デフォルト設定
    this.defaults = {
      breakpoint: 960,
      fadeDuration: 0.8,
      fadeEase: "power2.out",
      staggerAmount: 0.1,
      parallaxSpeed: 20,
    };
    // オプション上書き
    this.settings = { ...this.defaults, ...options };

    this.animateFade();
    this.animateResponsive();
    this.animateParallax();
  }

  // 統合版フェードアニメーション
  animateFade() {
    const animations = [
      { selector: ".fadeup", from: { y: 40, autoAlpha: 0 }, to: { y: 0, autoAlpha: 1 } },
      { selector: ".fadeleft", from: { x: 40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
      { selector: ".faderight", from: { x: -40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
      { selector: ".fadezoom", from: { scale: 0.8, autoAlpha: 0 }, to: { scale: 1, autoAlpha: 1 } },
    ];

    animations.forEach(({ selector, from, to }) => {
      gsap.utils.toArray(selector).forEach((el) => {
        gsap.fromTo(
          el,
          from,
          {
            ...to,
            duration: this.settings.fadeDuration,
            ease: this.settings.fadeEase,
            stagger: this.settings.staggerAmount,
            scrollTrigger: {
              trigger: el,
              start: "top 80%",
              toggleActions: "play none none none",
              // markers: true,
            },
          }
        );
      });
    });
  }

  // PC/SPレスポンシブアニメーション
  animateResponsive() {
    const mm = gsap.matchMedia();
    const bp = this.settings.breakpoint;

    mm.add(`(min-width: ${bp}px)`, () => {
      const pcAnimations = [
        { selector: ".fadeup-pc", from: { y: 40, autoAlpha: 0 }, to: { y: 0, autoAlpha: 1 } },
        { selector: ".fadeleft-pc", from: { x: 40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
        { selector: ".faderight-pc", from: { x: -40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
        { selector: ".fadezoom-pc", from: { scale: 0.8, autoAlpha: 0 }, to: { scale: 1, autoAlpha: 1 } },
      ];

      pcAnimations.forEach(({ selector, from, to }) => {
        gsap.utils.toArray(selector).forEach((el) => {
          gsap.fromTo(
            el,
            from,
            {
              ...to,
              duration: this.settings.fadeDuration,
              ease: this.settings.fadeEase,
              stagger: this.settings.staggerAmount,
              scrollTrigger: {
                trigger: el,
                start: "top 80%",
                toggleActions: "play none none none",
                // markers: true,
              },
            }
          );
        });
      });
    });

    mm.add(`(max-width: ${bp - 1}px)`, () => {
      const spAnimations = [
        { selector: ".fadeup-sp", from: { y: 40, autoAlpha: 0 }, to: { y: 0, autoAlpha: 1 } },
        { selector: ".fadeleft-sp", from: { x: 40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
        { selector: ".faderight-sp", from: { x: -40, autoAlpha: 0 }, to: { x: 0, autoAlpha: 1 } },
        { selector: ".fadezoom-sp", from: { scale: 0.8, autoAlpha: 0 }, to: { scale: 1, autoAlpha: 1 } },
      ];

      spAnimations.forEach(({ selector, from, to }) => {
        gsap.utils.toArray(selector).forEach((el) => {
          gsap.fromTo(
            el,
            from,
            {
              ...to,
              duration: this.settings.fadeDuration,
              ease: this.settings.fadeEase,
              stagger: this.settings.staggerAmount,
              scrollTrigger: {
                trigger: el,
                start: "top 80%",
                toggleActions: "play none none none",
                // markers: true,
              },
            }
          );
        });
      });
    });
  }

  // パララックスアニメーション
  animateParallax() {
    const defaultSpeed = this.settings.parallaxSpeed;

    gsap.utils.toArray(".parallax").forEach((picture) => {
      const img = picture.querySelector("img");
      if (!img) return;

      const speedAttr = picture.dataset.speed;
      let speed = parseFloat(speedAttr);

      if (isNaN(speed)) {
        speed = defaultSpeed;
      }

      gsap.fromTo(
        img,
        { y: `-${speed}%` },
        {
          y: `${speed}%`,
          ease: "none",
          overwrite: "auto", // transform競合を防止
          scrollTrigger: {
            trigger: picture,
            start: "top bottom",
            end: "bottom top",
            scrub: true,
            // markers: true,
          },
        }
      );
    });
  }
}