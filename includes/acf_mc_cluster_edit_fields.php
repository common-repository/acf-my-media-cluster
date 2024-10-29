<?php 
/* Name: acf_mc_cluster_edit_fields.php
 * Renders the media edit modal window on the post edit page. Activated by clicking the edit attachment button.
 */
?>

<?php
    // exit if accessed directly
    if( ! defined( 'ABSPATH' ) ) exit;

	if( empty($_GET['post_id']) or empty($_GET['attachment_id']) or empty($_GET['acf-mc-key']) or empty($_GET['acf-mc-name']) ) return;

	$post_id = sanitize_text_field($_GET['post_id']);
	$acf_mc_key = sanitize_key($_GET['acf-mc-key']);
	$acf_mc_name = sanitize_text_field($_GET['acf-mc-name']);
	$id = sanitize_text_field($_GET['attachment_id']);
	$item = get_post($id);

	if( empty($item) ) return;

	$url = wp_get_attachment_url($item->ID);
	$alt_text = get_field('_wp_attachment_image_alt', $item->ID);
	$title = get_the_title($item->ID);
	$caption = get_the_excerpt($item->ID);
	$description = get_the_content(null, false, $item->ID);
?>
<div class="acf-mc-modal-cotaniner-content">
	<?php
		$tables = array();
		$post = array(
		    //I guess we have to sanitize this all a second time?
		    'acf_field_key' => esc_attr(sanitize_key($acf_mc_key)),
		    'acf_field_name' => esc_attr(sanitize_text_field($acf_mc_name)),
		    'post_id' => esc_attr(sanitize_text_field(preg_replace('/\D/', '', $post_id))),
		    'attachment_id' => esc_attr(sanitize_text_field(preg_replace('/\D/', '', $id)))
		);
		$defaults = array(
			array(
				'label' => 'Alt Text',
			    'control' => array('table' => 'postmeta', 'type' => 'text', 'name' => '_wp_attachment_image_alt', 'value' => esc_attr(sanitize_text_field($alt_text)))
			),
			array(
				'label' => 'Title',
			    'control' => array('table' => 'posts', 'type' => 'text', 'name' => 'post_title', 'value' => esc_attr(sanitize_text_field($title)))
			),
			array(
				'label' => 'Caption',
			    'control' => array('table' => 'posts', 'rows' => 3, 'type' => 'textarea', 'name' => 'post_excerpt', 'value' => esc_attr(sanitize_text_field($caption)))
			),
			array(
				'label' => 'Description',
			    'control' => array('table' => 'posts', 'rows' => 3, 'type' => 'textarea', 'name' => 'post_content', 'value' => esc_html(sanitize_text_field($description)))
			)
		);

		$fields = apply_filters('acf/media-cluster-edit-fields', $post, $defaults);

		$html = '';
		
		if( !empty($fields) ){
			foreach( $fields as $attr ){
				$html .= '<div class="acf-mc-modal-container-controls">';
				if( !empty($attr['label']) ){
					$html .= "<label>" . esc_attr($attr['label']) . "</label>";
				}
				if( !empty($attr['control']) ){
					$c = "";
					foreach($attr['control'] as $a => $b){
						if($a == "table"){
							$tables[$attr['control']['name']] = $b;
						}
						if( ($attr['control']['type'] == "textarea" and $a == "value") or $a == "table" ){
							continue;
						}
						$c .= esc_attr($a) . ' = "' . esc_attr($b) . '"';
					}
					if( $attr['control']['type'] == "text" ){
						$html .= '<input ' . $c . '/>';
					} else if( $attr['control']['type'] == "textarea" ){
						$html .= '<textarea ' . $c . '>' . esc_html($attr['control']['value']) . '</textarea>';
					}
				}
				$html .= '</div>';
			}
		}

	?>
	<?php if( empty($fields) ) return; ?>
	<form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
		<input type="hidden" name="action" value="acf_mc_cluster_edit_save_field"/>
		<input type="hidden" name="post_id" value="<?php echo esc_attr(preg_replace('/[^0-9]/', '', $id)); ?>"/>
		<input type="hidden" name="acf-mc-field-key" value="<?php echo esc_attr(preg_replace('/[^a-z0-9_]/', '', $acf_mc_key)); ?>"/>
		<input type="hidden" name="acf-mc-field-name" value="<?php echo esc_attr(preg_replace('/[^a-z0-9_]/', '', $acf_mc_name)); ?>"/>
		<input type="hidden" name="return-field" value="title-<?php echo esc_attr($post['attachment_id']); ?>"/>
		<textarea name="tables" style="display:none;"><?php echo esc_textarea(wp_json_encode($tables)); ?></textarea>
		<h1 class="acf-mc-modal-cotaniner-header">Edit</h1>
		<?php 
		
		if( !empty($fields) ){
		    foreach( $fields as $attr ){
		        echo '<div class="acf-mc-modal-container-controls">';
		        if( !empty($attr['label']) ){
		            echo "<label>" . esc_html($attr['label']) . "</label>";
		        }
		        if( !empty($attr['control']) ){
		            if( $attr['control']['type'] == "text" ){
		                echo '<input ';
		                foreach($attr['control'] as $a => $b){
		                    if($a == "table"){
		                        $tables[$attr['control']['name']] = esc_attr($b);
		                    }
		                    if( ($attr['control']['type'] == "textarea" and $a == "value") or $a == "table" ){
		                        continue;
		                    }
		                    echo esc_attr($a) . ' = "' . esc_attr($b) . '" ';
		                }
		                echo '/>';
		            } else if( $attr['control']['type'] == "textarea" ){
	                    echo '<textarea ';
	                    foreach($attr['control'] as $a => $b){
	                        if($a == "table"){
	                            $tables[$attr['control']['name']] = esc_attr($b);
	                        }
	                        if( ($attr['control']['type'] == "textarea" and $a == "value") or $a == "table" ){
	                            continue;
	                        }
	                        echo esc_attr($a) . ' = "' . esc_attr($b) . '" ';
	                    }
	                    echo '>' . esc_textarea($attr['control']['value']) . '</textarea>';
		            }
		        }
		        echo '</div>';
		    }
		}
		?>
		<div class="acf-mc-modal-container-controls">
			<button type="submit" class="button-primary">Save Changes</button>
			<button type="button" class="button acf-mc_modal-close">Close</button>
		</div>
	</form>
</div>
<?php
