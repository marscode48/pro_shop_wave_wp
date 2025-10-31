<?php

/**
 * Single Post Content
 * Outputs the main post content and optional pagination.
 */
?>
<div class="single-blog__content">
  <?php the_content(); ?>

  <?php
  // 長文記事のページ分割がある場合にのみ使用
  wp_link_pages([
    'before' => '<div class="page-links">',
    'after'  => '</div>',
  ]);
  ?>
</div>