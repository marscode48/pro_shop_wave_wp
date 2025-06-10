import { gsap } from 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/index.js';

export class GsapNewArrivals {
  constructor(swiperInstance) {
    this.swiper = swiperInstance;
    this.initGsap();
  }

  initGsap() {
    this.swiper.on('slideChangeTransitionStart', () => {
      const activeSlide = this.swiper.slides[this.swiper.activeIndex];
      const card = activeSlide.querySelector('.card-item');

      if (card) {
        const items = card.querySelectorAll('.card-item__date, .card-item__title, .card-item__subtitle, .card-item__more-link');

        // 要素ごとの順番アニメーション
        gsap.fromTo(
          items,
          { y: 20, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.6,
            ease: 'power2.out',
            stagger: 0.1
          }
        );

        // カード全体のスケール＆フェードイン
        gsap.fromTo(
          card,
          { scale: 0.8, opacity: 0 },
          { scale: 1, opacity: 1, duration: 0.5, ease: 'power3.out' }
        );
      }
    });
  }
}