<div id="wp-magniffic-gallery-preview">
</div>
<textarea name="wp-magniffic-gallery-editor">
<?php echo get_post_meta( get_the_ID(), 'wp-magniffic-gallery', true ); ?>
</textarea>
<?php wp_nonce_field( 'wp-magniffic-gallery-save', 'wp-magniffic-gallery-nonce' ); ?>