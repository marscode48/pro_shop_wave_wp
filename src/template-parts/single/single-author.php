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

      <?php
      // SNSリンク（管理画面「ユーザー」プロフィールに Webサイト / Twitter / Instagram / Facebook / YouTube を登録しておく想定）
      // ※ URL でも「@ハンドル」でも入力OK。get_author_social_url() で正規化してから出力する。
      $user_id = (int) get_the_author_meta('ID');

      // WebサイトはフルURL想定（WPデフォルトの user_url）。空や不正URLは後段の empty() 判定で弾かれる。
      $author_url    = esc_url((string) get_the_author_meta('user_url'));

      // SNSは URL / ドメインのみ / @handle いずれも許容 → 正規化
      $twitter_url   = function_exists('get_author_social_url') ? get_author_social_url($user_id, 'twitter')   : '';
      $instagram_url = function_exists('get_author_social_url') ? get_author_social_url($user_id, 'instagram') : '';
      $facebook_url  = function_exists('get_author_social_url') ? get_author_social_url($user_id, 'facebook')  : '';
      $youtube_url   = function_exists('get_author_social_url') ? get_author_social_url($user_id, 'youtube')   : '';
      ?>

      <?php if ($author_url || $twitter_url || $instagram_url || $facebook_url || $youtube_url) : ?>
        <div class="author__sns" aria-label="<?php echo esc_attr__('著者のSNS', 'proshopwave'); ?>">
          <?php if ($author_url) : ?>
            <a class="author__sns-link author__sns-link--website" href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr__('ウェブサイト', 'proshopwave'); ?>">
              <i class="fas fa-globe"></i>
            </a>
          <?php endif; ?>

          <?php if ($twitter_url) : ?>
            <a class="author__sns-link author__sns-link--twitter" href="<?php echo esc_url($twitter_url); ?>" target="_blank" rel="noopener nofollow" aria-label="Twitter">
              <i class="fab fa-x-twitter"></i>
            </a>
          <?php endif; ?>

          <?php if ($instagram_url) : ?>
            <a class="author__sns-link author__sns-link--instagram" href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener nofollow" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
          <?php endif; ?>

          <?php if ($facebook_url) : ?>
            <a class="author__sns-link author__sns-link--facebook" href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener nofollow" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
          <?php endif; ?>

          <?php if ($youtube_url) : ?>
            <a class="author__sns-link author__sns-link--youtube" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener nofollow" aria-label="YouTube">
              <i class="fab fa-youtube"></i>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>