import Swiper from 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.mjs';

export class SwiperNewArrivals {
  // SwiperNewArrivalsの初期化。指定された要素にSwiperを適用する
  constructor(el = '.new-arrivals-swiper .swiper') {
    this.el = el;
    this.swiper = this.initSwiper();
  }

  initSwiper() {
    return new Swiper(this.el, {
      slidesPerView: 1,
      spaceBetween: 24,
      grabCursor: true,
      loop: true,
      centeredSlides: true,
      pagination: {
        el: '.section--new .swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.section--new .swiper-button-next',
        prevEl: '.section--new .swiper-button-prev',
      },
      breakpoints: {
        768: {
          slidesPerView: 3,
          spaceBetween: 24,
        },
        960: {
          slidesPerView: 4,
          spaceBetween: 32,
        },
      },
    });
  }

  get instance() {
    return this.swiper;
  }

  start(options = {}) {
    options = Object.assign({
      delay: 4000,
      disableOnInteraction: false,
      enabled: true,
      pauseOnMouseEnter: false,
      reverseDirection: false,
      stopOnLastSlide: false,
      waitForTransition: true,
    }, options);
    this.swiper.params.autoplay = options;
    this.swiper.autoplay.start();
  }

  stop() {
    this.swiper.autoplay.stop();
  }
}