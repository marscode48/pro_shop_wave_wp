<?php
/**
 * Template part for New Arrivals section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--new">
  <div class="section__inner">
    <h2 class="section__title fadeup">New Arrivals</h2>
    <div class="swiper-area new-arrivals-swiper fadeup">
      <div class="swiper">
        <div class="swiper-wrapper">
          <?php for ($i = 1; $i <= 7; $i++) : ?>
            <div class="swiper-slide">
              <div class="card-item">
                <picture>
                  <source srcset="<?php echo get_theme_file_uri("images/card_item_0{$i}_example.webp"); ?>" type="image/webp">
                  <img src="<?php echo get_theme_file_uri("images/card_item_0{$i}_example.jpg"); ?>" alt="Item <?php echo $i; ?>" class="card-item__image">
                </picture>
                <div class="card-item__content">
                  <time class="card-item__date" datetime="2025-04-0<?php echo $i; ?>">2025.04.0<?php echo $i; ?></time>
                  <h3 class="card-item__title">Item Title <?php echo $i; ?></h3>
                  <p class="card-item__subtitle">Item Subtitle <?php echo $i; ?></p>
                  <div class="card-item__more">
                    <a href="#" class="card-item__more-link">
                      More<i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>