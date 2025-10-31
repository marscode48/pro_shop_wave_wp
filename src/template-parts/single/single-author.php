<?php

/**
 * Template part: シングル記事 著者ボックス
 * @package proshopwave
 */

if (! get_the_author_meta('description')) {
  return; // 著者情報が無ければ出力しない
}
?>

<section class="single-blog__author" aria-labelledby="author-heading">
  <h2 id="author-heading" class="single-blog__author-title">
    <?php echo esc_html__('著者について', 'proshopwave'); ?>
  </h2>

  <div class="author">
    <div class="author__avatar">
      <?php echo get_avatar(get_the_author_meta('ID'), 96); ?>
    </div>

    <div class="author__info">
      <p class="author__name"><?php the_author(); ?></p>
      <p class="author__bio"><?php the_author_meta('description'); ?></p>

      <?php if (get_the_author_meta('user_url')) : ?>
        <a class="author__more" href="<?php echo esc_url(get_the_author_meta('user_url')); ?>" target="_blank" rel="noopener nofollow">
          <?php echo esc_html__('著者ページを見る', 'proshopwave'); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>