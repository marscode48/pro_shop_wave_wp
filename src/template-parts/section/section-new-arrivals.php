<?php
/**
 * Template part for New Arrivals section
 *
 * @package PRO_SHOP_WAVE
 */
?>
<section class="section section--new">
  <div class="section__inner">
    <h2 class="section__title fadeup"><?php echo esc_html__( 'New Arrivals', 'proshopwave' ); ?></h2>
    <div class="swiper-area new-arrivals-swiper fadeup">
      <div class="swiper">
        <div class="swiper-wrapper">
          <?php
          $args = [
            'post_type' => 'product',
            'posts_per_page' => 8,
            'post_status' => 'publish',
          ];
          $products = new WP_Query($args);
          if ($products->have_posts()) :
            while ($products->have_posts()) : $products->the_post();
              global $product;
          ?>
            <div class="swiper-slide">
              <div class="card-item">
                <picture>
                  <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title_attribute(); ?>" class="card-item__image">
                </picture>
                <div class="card-item__content">
                  <time class="card-item__date" datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                  <h3 class="card-item__title"><?php the_title(); ?></h3>
                  <p class="card-item__subtitle"><?php echo wp_trim_words(get_the_excerpt(), 35); ?></p>
                  <div class="card-item__more">
                    <a href="<?php the_permalink(); ?>" class="card-item__more-link">
                      <?php echo esc_html__( 'More', 'proshopwave' ); ?><i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php
            endwhile;
            wp_reset_postdata();
          endif;
          ?>
        </div>
      </div>
      <div class="swiper-button-prev" aria-label="<?php echo esc_attr__( 'Previous', 'proshopwave' ); ?>">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
      </div>
      <div class="swiper-button-next" aria-label="<?php echo esc_attr__( 'Next', 'proshopwave' ); ?>">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </div>
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>