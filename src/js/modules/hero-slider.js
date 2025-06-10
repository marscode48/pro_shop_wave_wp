import { gsap } from 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/index.js';
import Swiper from 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.mjs';

export class HeroSlider {
  // HeroSliderの初期化。指定された要素にSwiperを適用する
  constructor(el = '.hero-swiper') {
    this.el = el;
    this.swiper = this._initSwiper();
  }

  // Swiperインスタンスの初期化とイベントハンドラの登録
  _initSwiper() {
    return new Swiper(this.el, {
      effect: 'fade',
      fadeEffect: {
        crossFade: true,
      },
      loop: true,
      speed: 1000,
      followFinger: false,
      on: {
        init: () => {
          this._activateHeroMarker();
        },
        slideChange: () => {
          this._deactivateHeroMarker();
        },
        slideChangeTransitionStart: () => {
          this._activateHeroMarker();
        }
      }
    });
  }

  // 現在のスライドに対応するマーカーとサブヘディングをアニメーションさせる
  _activateHeroMarker() {
    const markerDelay = 0.3;
    const initialDelay = 1;
    const heroContentEl = document.querySelector('.hero-swiper .swiper-slide-active .hero__content');
    if (!heroContentEl) return;

    heroContentEl.classList.remove('is-marker-completed');
    heroContentEl.classList.add('is-marker-active');

    const markers = heroContentEl.querySelectorAll('.hero__text-marker');
    const subheading = heroContentEl.querySelector('.hero__subheading-jp');

    // マーカーのアニメーション関連のインラインスタイルを設定
    if (markers.length > 0) {
      const duration = markerDelay * markers.length + markerDelay + initialDelay;
      markers.forEach((marker, index) => {
        const delay = markerDelay * index + markerDelay;
        marker.style.setProperty('animation-delay', `${delay}s`);
        marker.style.setProperty('animation-duration', `${duration}s`);
        marker.style.setProperty('--marker-animation-duration', `${duration}s`); // ::after用
      });
    }

    // マーカーのアニメーション後にサブヘディングをGSAPでアニメーション
    if (subheading) {
      const delay = markerDelay * markers.length + markerDelay;
      gsap.fromTo(subheading,
        { scale: 0.5, opacity: 0, filter: 'blur(300px)' },
        {
          scale: 1,
          opacity: 1,
          filter: 'blur(0px)',
          duration: 1,
          delay,
          ease: 'power2.out'
        }
      );
    }
  }

  // アクティブなマーカーやサブヘディングのアニメーションスタイルをリセット
  _deactivateHeroMarker() {
    const heroContentEl = document.querySelector('.hero-swiper .hero__content.is-marker-active');
    if (!heroContentEl) return;

    // クラスを切り替える前に、マーカーのアニメーション関連のインラインスタイルを削除
    const markers = heroContentEl.querySelectorAll('.hero__text-marker');
    markers.forEach((marker) => {
      marker.style.removeProperty('animation-delay');
      marker.style.removeProperty('animation-duration');
      marker.style.removeProperty('--marker-animation-duration');
    });

    heroContentEl.classList.remove('is-marker-active');
    heroContentEl.classList.add('is-marker-completed');

    // サブヘディングをGSAPでアニメーションでフェードアウト
    const subheading = heroContentEl.querySelector('.hero__subheading-jp');
    if (subheading) {
      gsap.to(subheading, {
        scale: 0.25,
        opacity: 0,
        y: 20,
        duration: 0.8,
        ease: 'power2.out',
        // アニメーション完了後にスタイルをリセット
        onComplete: () => {
          gsap.set(subheading, {
            scale: 0.5,
            y: 0,
            clearProps: 'transform'
          });
        }
      });
    }
  }

  // スライダーの自動再生を開始（オプションでdelayなどを上書き可能）
  start(options = {}) {
    options = Object.assign({
      delay: 4000,
      disableOnInteraction: false,
      enabled: false,
      pauseOnMouseEnter: false,
      reverseDirection: false,
      stopOnLastSlide: false,
      waitForTransition: true,
    }, options);
    this.swiper.params.autoplay = options;
    this.swiper.autoplay.start();
  }

  // スライダーの自動再生を停止
  stop() {
    this.swiper.autoplay.stop();
  }
}
