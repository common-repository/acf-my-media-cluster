<?php 
/* Name: acf_mc_cluster_field_group.php
 * Renders the individual file input rows within render_field.php
 * See also: render_field.php
 */

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;
?>

<?php
    $key = "";
	if( !empty($_REQUEST['key']) ){
	    $key = sanitize_key($_REQUEST['key']);
	} else if( !empty($pkey) ){
	    $key = sanitize_key($pkey);
	}
	$group = (!empty($_REQUEST['group']))?sanitize_text_field($_REQUEST['group']):1;
	$fname = (!empty($_REQUEST['fname']))?sanitize_text_field($_REQUEST['fname']):sanitize_text_field($fname);
	if( $groupIndex > 0 ){
		$group = $groupIndex;
	}
?>
<div class="acf-mc-field-group acf-mc-field-group-row acf-mc-field-group-<?php echo esc_attr($group); ?>">
	<div class="acf-mc-field-column acf-mc-field-column-icon">
		<span class="dashicons dashicons-menu"></span>
	</div>
	<input class="acf-mc-field-filename" type="hidden" readonly name="filename" value="<?php echo esc_html(esc_url($url)); ?>"/>
	<div class="acf-mc-field-column acf-mc-field-column-id">
		<?php echo esc_html($attachment_id); ?>
	</div>
	<div class="acf-mc-field-column acf-mc-field-column-title">
		<input class="acf-mc-field-title" type="hidden" readonly name="title" id="title-<?php echo esc_attr($attachment_id); ?>" value="<?php echo esc_html($title); ?>"/>
		<a id="title-link-<?php echo esc_attr($attachment_id); ?>" href="<?php echo esc_url($url); ?>" target="_blank" title="View file"><?php echo esc_html($title); ?></a>
	</div>
	<div class="acf-mc-field-column acf-mc-field-column-type">
		<?php echo esc_html($type); ?>
	</div>
	<div class="acf-mc-field-column acf-mc-field-column-size">
		<?php echo esc_html($size); ?>
	</div>
	<div class="acf-mc-field-column acf-mc-field-column-downloads">
		<?php echo esc_html($downloads); ?>
	</div>
	<div class="acf-mc-field-column acf-mc-field-column-action">
		<?php if( preg_replace('/[^0-9]/', '', $attachment_id) > 0 ){ ?>
			<input type="hidden" name="acf-mc-fields[<?php echo esc_attr($fname); ?>][]" value="<?php echo esc_attr($attachment_id); ?>"/>
		<?php } ?>
		<a href="#" title="Choose File from Media Library" class="button button-choose-file button-primary" data-key="<?php echo esc_attr(sanitize_key($key)); ?>" data-name="<?php echo esc_attr(sanitize_text_field($fname)); ?>" data-group="acf-mc-field-group-<?php echo esc_attr(sanitize_text_field($group)); ?>">Choose File</a>
		<a href="#" title="Edit" class="button button-edit <?php if(!$showEditDel) { echo 'acf-mc-field-hide'; } ?>" data-post_id="<?php echo esc_attr(sanitize_text_field($_GET['post'])); ?>" data-attachment_id="<?php echo esc_attr(sanitize_text_field($attachment_id)); ?>" data-key="<?php echo esc_attr(sanitize_key($key)); ?>" data-name="<?php echo esc_attr(sanitize_text_field($fname)); ?>" ><span class="dashicons dashicons-edit"></span></a>
		<a href="#" title="Delete" class="button button-delete  <?php if(!$showEditDel) { echo 'acf-mc-field-hide'; } ?>" data-key="<?php echo esc_attr(sanitize_key($key)); ?>" data-group="acf-mc-field-group-<?php echo esc_attr(sanitize_text_field($group)); ?>"><span class="dashicons dashicons-trash"></span></a>
		<a href="#" title="Add More" class="button button-plus <?php if(!$showAdd) { echo 'acf-mc-field-hide'; } ?>" data-key="<?php echo esc_attr(sanitize_key($key)); ?>" data-name="<?php echo esc_attr(sanitize_text_field($fname)); ?>" data-group="acf-mc-field-group-<?php echo esc_attr(sanitize_text_field($group)); ?>"><span class="dashicons dashicons-plus"></span></a>
	</div>
</div>
<?php
	if( !empty($_REQUEST['noajax']) and $_REQUEST['noajax'] == true){ 
        die();
    }