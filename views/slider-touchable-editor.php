<div id="wp-slider-touchable-preview">
</div>
<textarea name="wp-slider-touchable-editor">
<?php echo get_post_meta( get_the_ID(), 'wp-slider-touchable', true ); ?>
</textarea>
<?php wp_nonce_field( 'wp-slider-touchable-save', 'wp-slider-touchable-nonce' ); ?>