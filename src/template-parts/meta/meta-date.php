<?php

/**
 * Common date meta with auto "is-updated" class
 * @package PRO_SHOP_WAVE
 */

$published = get_the_date('Y-m-d H:i:s');
$modified  = get_the_modified_date('Y-m-d H:i:s');
$is_updated = ($modified !== $published);

?>
<time class="<?php echo esc_attr(($args['class'] ?? '') . ($is_updated ? ' is-updated' : '')); ?>" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
  <?php echo esc_html(get_the_date('Y.m.d')); ?>
  <?php if ($is_updated) : ?>
    <span class="is-updated-label"><?php echo esc_html__('更新', 'proshopwave'); ?></span>
  <?php endif; ?>
</time>