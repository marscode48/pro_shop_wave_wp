<?php
/**
 * Template part for Blog section
 */
?>
<section class="section section--blog">
  <div class="section__inner">
    <h2 class="section__title fadeup">Blog</h2>
    <p class="section__text fadeup">ドリフトパーツやアパレルの<br class="sp-only">最新情報をピックアップ。</p>
    <div class="card-list card-list--blog">
      <?php for ($i = 1; $i <= 3; $i++) : ?>
        <article class="card-blog fadeup">
          <a href="/blog/sample-<?php echo $i; ?>/" class="card-blog__link">
            <picture class="card-blog__picture">
              <source srcset="<?php echo get_theme_file_uri("images/card_blog_0{$i}_sample.webp"); ?>" type="image/webp">
              <img src="<?php echo get_theme_file_uri("images/card_blog_0{$i}_sample.jpg"); ?>" alt="Blog <?php echo $i; ?>" class="card-blog__image">
            </picture>
            <div class="card-blog__content">
              <time class="card-blog__date" datetime="2025-04-0<?php echo $i; ?>">2025.04.0<?php echo $i; ?></time>
              <h3 class="card-blog__title">Blog Title <?php echo $i; ?></h3>
              <p class="card-blog__text">Blog content preview for entry <?php echo $i; ?>.</p>
              <div class="card-blog__tags">#タグ<?php echo $i; ?></div>
              <div class="card-blog__more">
                <span class="card-blog__more-link">Read More<i class="fas fa-arrow-right"></i></span>
              </div>
            </div>
          </a>
        </article>
      <?php endfor; ?>
    </div>
    <div class="section__button-area faderight">
      <a href="/blog/" class="section__button">
        <span class="section__button-inner">View All</span>
      </a>
    </div>
  </div>
</section>