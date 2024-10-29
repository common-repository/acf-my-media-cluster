<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('acf_media_cluster_field') ) :

class acf_media_cluster_field extends acf_field {
	
	/*
	*  __construct
	*
	*  This function will setup the field type data for ACF version 5
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	1.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct( $settings ) {
		
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		
		$this->name = 'media_cluster';
		
		
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		
		$this->label = __('Media Cluster', 'acf_media_cluster');
		
		
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		
		$this->category = 'content';
		
		
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		
		$this->defaults = array(
			'font_size'	=> 14,
		);
		
		
		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/
		
		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'acf_media_cluster'),
		);
		
		
		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/
		
		$this->settings = $settings;
		
		
		// do not delete!
    	parent::__construct();
    	
	}	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	1.0
	*  @date	1/13/2023
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ){
		require( dirname(dirname(__FILE__)) . '/includes/render_field.php');
	}
	
}


// initialize
new acf_media_cluster_field( $this->settings );


// class_exists check
endif;

/*
 *  acf_mc_render_field_settings_v5()
 *
 *  Adds custom settings to the field being rendered
 *
 *  @type	action
 *  @since	1.2.3
 *  @date	4/23/2023
 *
 *  @param	$field (array) the $field being rendered
 *  @return	n/a
 */
function acf_mc_render_field_settings_v5( $field )
{
    $name = $field['name'];
    $value = $field['field_custom_settings'];
    
    acf_render_field_setting( $field, array(
        'label'			=> __('Force Download','TEXTDOMAIN'),
        'type'          => 'select',
        'name'          => 'acf_mc_force_download',
        'value'         => $value['acf_mc_force_download'],
        'choices'       => array('No' => 'No', 'Yes' => 'Yes')
    ));
    
    wp_nonce_field( 'editpost_'.get_the_ID(), 'acf-mc-nonce' );
}
add_action('acf/render_field_settings/type=media_cluster', 'acf_mc_render_field_settings_v5');

?>