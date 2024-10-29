<?php 
/* Name: render_field.php
 * Renders the media cluster field on the post edit page
 * See also: acf_mc_cluster_field_group.php
 */

	// exit if accessed directly
	if( ! defined( 'ABSPATH' ) ) exit;

	$key = preg_replace('/[^a-z0-9_]/', '', $field['key']);
	$fname = preg_replace('/[^a-z0-9_]/', '', $field['_name']);
	$acf_mc_attachment_ids = array_filter(explode(',', get_field($fname)));
	
	if( !empty($acf_mc_attachment_ids) and count($acf_mc_attachment_ids) > 0 ){
		$data = get_posts(array(
			'post__in' => $acf_mc_attachment_ids,
			'post_type' => 'attachment',
			'orderby' => 'post__in',
			'order' => 'ASC',
		    'numberposts' => -1
		));
	} else {
		$data = array();
	}
?>
<div id="acf-mc-nonce-container">
		<?php wp_nonce_field( 'editpost_'.get_the_ID(), 'acf-mc-nonce' ); ?>
</div>
<div class="acf-mc-<?php echo esc_attr(preg_replace('/[^a-z0-9_]/', '', $key)); ?>">
	<input type="hidden" name="acf-mc-fields[<?php echo esc_attr(preg_replace('/[^a-z0-9_]/', '', $fname)); ?>][]" value="0"/>
	<input type="hidden" name="acf-mc-field-key" value="<?php echo esc_html(preg_replace('/[^a-z0-9_]/', '', $key)); ?>"/>
	<input type="hidden" name="acf-mc-field-name" value="<?php echo esc_html(preg_replace('/[^a-z0-9_]/', '', $fname)); ?>"/>
	<div class="acf-mc-field-group acf-mc-field-group-label">
		<div class="acf-mc-field-column acf-mc-field-column-icon"><label></label></div>
		<div class="acf-mc-field-column acf-mc-field-column-id"><label>ID</label></div>
		<div class="acf-mc-field-column acf-mc-field-column-title"><label>Title</label></div>
		<div class="acf-mc-field-column acf-mc-field-column-type"><label>Type</label></div>
		<div class="acf-mc-field-column acf-mc-field-column-size"><label>Filesize</label></div>
		<div class="acf-mc-field-column acf-mc-field-column-downloads"><label>Downloads</label></div>
		<div class="acf-mc-field-column acf-mc-field-column-action"><label>Actions</label></div>
	</div>
    <div class="acf-mc-sortable acf-mc-field-group acf-mc-field-group-container">
		<?php 
			if( count($data) < 1 ){
				acf_mc_cluster_field_group(false, 0, $fname, $key); 
			} else {
				foreach($data as $index => $item){
					$url = wp_get_attachment_url($item->ID);
					$title = get_the_title($item->ID);
					$filename = get_attached_file( $item->ID );
					$filetype = wp_check_filetype( $filename );
					$type = $filetype['ext'];
					$downloads = (int) get_post_meta( $item->ID, '_acf_mc_downloads', true );
					$size = size_format( filesize( get_attached_file( $item->ID ) ), 0 );

					acf_mc_cluster_field_group(false, $item->ID, $fname, $key, $url, $title, $type, $size, $downloads, true, false, $index+1); 
				}
				acf_mc_cluster_field_group(false, 0, $fname, $key, null, null, null, null, null, false, true, count($data) + 1); 
			}
		?>
	</div>	
</div>
