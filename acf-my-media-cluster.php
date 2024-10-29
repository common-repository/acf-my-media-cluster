<?php
/*
Plugin Name: ACF My Media Cluster
Description: An extension for the Advanced Custom Fields plugin, which adds the ability to create groups of media files for download on a page/post/custom post type. Based on an add-on created by Navneil Naicker and Download Attachments by dFactory.
Version: 1.2.10
Author: Nikki Blight
Author URI: http://nlb-creations.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

//get the current plugin version number and define it as a constant
if( ! defined( 'MY_ACF_MEDIA_CLUSTER_VERSION' ) ) {
    $default_headers = array(
        'Name'        => 'Plugin Name',
        'Version'     => 'Version',
        'Description' => 'Description',
        'Author'      => 'Author',
        'TextDomain'  => 'Text Domain'
    );
    $plugin_data = get_file_data(  __FILE__, $default_headers, 'plugin' );
    
    define('MY_ACF_MEDIA_CLUSTER_VERSION', $plugin_data['Version']);
}

//check to see if a download link has been clicked
if(isset($_GET['acf_mc_download']) && $_GET['acf_mc_download'] == 1) {
    if(!isset($_GET['acf_mc_id']) || !isset($_GET['acf_mc_field_name'])) {
        return false;
    }
    
    $id = isset( $_GET['acf_mc_id'] ) ? esc_attr(sanitize_text_field( $_GET['acf_mc_id'] )) : 0;
    $field_name = isset( $_GET['acf_mc_field_name'] ) ? esc_attr(sanitize_text_field( $_GET['acf_mc_field_name'] )) : null;
    
    acf_mc_download_attachment($id, $field_name);
}

/*
 * Get the acf major version installed
 * 
 * @return string - The major version number (e.g. 6 if using ACF 6.1.4)
 */
function acf_mc_set_version() {
    preg_match("#^\d#", get_option('acf_version'), $match);
    return array_shift($match);
}

// check if class already exists
if( !class_exists('acf_media_cluster') ) {

    class acf_media_cluster {
    	
    	// vars
    	var $settings;
    	
    	/*
    	*  __construct
    	*
    	*  This function will setup the class functionality
    	*
    	*  @type	function
    	*  @date	17/02/2016
    	*  @since	1.0.0
    	*
    	*  @param	void
    	*  @return	void
    	*/
    	function __construct() {
    		
    		add_action('admin_init', array($this, 'scripts'));
    		add_filter('acf/media-cluster-edit-fields', array($this, 'edit_fields'), 10, 3);
    		
    		// settings
    		// - these will be passed into the field class.
    		$this->settings = array(
    		    'version'	=> MY_ACF_MEDIA_CLUSTER_VERSION,
    		    'acf_version' => acf_mc_set_version(),
    			'url'		=> plugin_dir_url( __FILE__ ),
    			'path'		=> plugin_dir_path( __FILE__ )
    		);	
    		
    		// include field
    		add_action('acf/include_field_types', 	array($this, 'include_field')); // v5/6
    		add_action('acf/register_fields', 		array($this, 'include_field')); // v4
    		
    	}
    
    	/*
    	 * scripts 
    	 * 
    	 * Enqueue necessary scripts and styles
    	 */
    	function scripts() {
    		$settings = $this->settings;
    		$version = $settings['version'];
    		wp_register_style('css-acf-media-cluster', plugins_url('/assets/css/acf-media-cluster.css', __FILE__), null, $version, null);
    		wp_enqueue_style('css-acf-media-cluster');
    		wp_register_script( 'js-acf-media-cluster', plugins_url('/assets/js/acf-media-cluster.js', __FILE__ ), 'jquery', $version, true);
    		wp_enqueue_script('js-acf-media-cluster');
    		
    	}
    	
    	/*
    	*  include_field
    	*
    	*  This function will include the field type class
    	*
    	*  @type	function
    	*  @date	4/21/2023
    	*  @since	1.0.0
    	*
    	*  @return	void
    	*/
    	function include_field() {
    	    
    		$acf_version = $this->settings['acf_version']; 
    		
    		// include
    	    if($acf_version == "6") {
    		    include_once('fields/class-acf-media-cluster-v6.php');
    		}
    		elseif($acf_version == "5") {
    		    include_once('fields/class-acf-media-cluster-v5.php');
    		}
    		else { //default to version 5
    		    include_once('fields/class-acf-media-cluster-v5.php');
    		}
    	}
        
    	/*
    	 * edit_fields
    	 * 
    	 * Edit field arguments
    	 * 
    	 * @param int $post
    	 * @param array $args
    	 * 
    	 * @return array $args
    	 */
    	function edit_fields( $post, $args ){
    		return $args;
    	}
    	
    }
    
    // initialize
    new acf_media_cluster();

} //class_exists check


/*
 * Save a new or existing media cluster field and settings
 * 
 * @param $post_id (int) the post the field is attached to
 * @return n/a
 */
function acf_mc_save_fields( $post_id ) {
    // verify this came from our site and with proper authorization
    if ( !isset( $_POST['acf-mc-nonce'] ) || !wp_verify_nonce( sanitize_key($_POST['acf-mc-nonce']), 'editpost_'.$post_id ) ) {
        return;
    }
    
    $fields = array();
    if(!empty($_POST['acf-mc-fields'])) {
        //content is an array, so send it to the helper function to sanitize everything, including array keys.
        //I'm genuinely at a loss as to another way to do this sanitization that would be acceptable and not destroy the array...
        $fields = acf_mc_sanitize_array_helper($_POST['acf-mc-fields']);
    }

	if( !empty($fields) and count($fields) ){
	    update_post_meta( $post_id, '_' . esc_attr(sanitize_text_field($_POST['acf-mc-field-name'])), esc_attr(sanitize_text_field($_POST['acf-mc-field-key'])));
		foreach($fields as $field_name => $ids){ //both the keys and values of the $fields array are sanitized by the acf_mc_sanitize_array_helper() function immediately preceeding this loop
			if( count($ids) === 1 and $ids[0] < 1){
			    delete_post_meta( $post_id, esc_attr(sanitize_text_field($field_name)) );
			} else if( count($ids) > 1 ){
				$idx = array();
				foreach($ids as $id){
					if( $id > 0 ){
					    $idx[] = esc_attr(sanitize_text_field(preg_replace('/\D/', '', $id)));
					}
				}
				update_post_meta( $post_id, $field_name, implode(',', $idx));
			}
		}	
	}
	add_action( 'save_post', 'acf_mc_save_fields' );
}
add_action( 'save_post', 'acf_mc_save_fields' );

/*
 * Include the code for the media cluster field group
 */
function acf_mc_cluster_field_group($noajax = false, $attachment_id = 0, $fname = "", $pkey = "", $url = "", $title = "", $type = "", $size = "", $downloads = "", $showEditDel = false, $showAdd = true, $groupIndex = 0){
	include( dirname(__FILE__) . '/includes/acf_mc_cluster_field_group.php' );
}
add_action('wp_ajax_acf_mc_cluster_field_group', 'acf_mc_cluster_field_group');

/*
 * Include code for the model edit window for individual files attached to the media cluster field
 *
 * @return n/a
 */
function acf_mc_cluster_edit_fields(){
	include( dirname(__FILE__) . '/includes/acf_mc_cluster_edit_fields.php' );
	die();
}
add_action('wp_ajax_acf_mc_cluster_edit_fields', 'acf_mc_cluster_edit_fields');

/*
 * Save edits made in the modal edit window for individual files
 *
 * @return n/a
 */
function acf_mc_cluster_edit_save_field(){
	if( $_POST['action'] == 'acf_mc_cluster_edit_save_field' ){
		global $wpdb;
		$t = array();
		
		//sanitize/decode/escape the $tables JSON
		$get_tables = sanitize_text_field($_POST['tables']);
		$tables = json_decode(stripslashes($get_tables), true);
		foreach($tables as $i => $tableitem) {
		    $tables[$i] = esc_attr($tableitem);
		}
		
		$post_id = (int) sanitize_key($_POST['post_id']);
		$acf_mc_key = sanitize_key($_POST['acf-mc-field-key']);
		foreach($tables as $a => $b){
			if( !empty($t[$b]) ){
				array_push($t[sanitize_text_field($b)], sanitize_text_field($a));
			} else {
				$t[sanitize_text_field($b)] = array($a);
			}
		}
		if( !empty($t) ){
			foreach($t as $a => $b){
				if( $a == "postmeta" ){
					foreach($b as $c){
						update_post_meta( $post_id, $c, sanitize_text_field($_POST[$c]));
					}
				}
				if( $a == "posts" ){
					$u = array();
					$u['ID'] = $post_id;
					foreach( $b as $c){
						if( in_array($c, array('post_content', 'post_title', 'post_excerpt')) ){
							if( in_array($c, array('post_content')) ){
								$u[$c] = sanitize_textarea_field($_POST[$c]);
							} else {
								$u[$c] = sanitize_text_field($_POST[$c]);
							}
						}
					}
					if( !empty($u) ){
						wp_update_post( $u );
					}
				}
			}
			update_option('acf_mc_key_' . $acf_mc_key, wp_json_encode($t) );
		}
	}
	die();
}
add_action('wp_ajax_acf_mc_cluster_edit_save_field', 'acf_mc_cluster_edit_save_field');

/*
 * Retrieves the contents of a media cluster field
 * 
 * @param string $field_name - the name of the ACF field to locate
 * @param int $post_id - the post ID of the post to which the field is attached
 * @param array $args - arguements for the get_posts query.  Valid arguements are orderby and order
 * 
 * return array $data
 */
function acf_media_cluster($field_name, $post_id = null, $args = array()){
	global $wpdb;
	$post_id = $post_id>0?$post_id:get_the_ID();
	$field_name = sanitize_text_field($field_name);
	$orderby = !empty($args['orderby'])?sanitize_text_field($args['orderby']):'post__in';
	$order = !empty($args['order'])?sanitize_text_field($args['order']):'ASC';
	$field_key = get_field('_' . sanitize_text_field($field_name));
	$option = json_decode(get_option('acf_mc_key_' . $field_key));
	$meta_attachment_ids = array_filter(explode(',', get_field($field_name, preg_replace('/\D/', '', $post_id))));
	
	$posts = array();
	if($meta_attachment_ids) { //running get_posts() on an empty array returns unrelated data, to check that the array has values
		$posts = get_posts(array(
			'post__in' => $meta_attachment_ids,
			'post_type' => 'attachment',
			'orderby' => $orderby,
		    'order' => $order,
		    'numberposts' => -1
		));
	}
	
	$data = array();
	foreach( $posts as $a ){
		
		//get the extension so we can set the filetype icon correctly
		$filename = get_attached_file( $a->ID );
		$filetype = wp_check_filetype( $filename );
		
		switch ( $filetype['ext'] ) {
			case 'jpeg' :
				$extension = 'jpg';
				break;
			case 'docx' :
				$extension = 'doc';
				break;
			case 'xlsx' :
				$extension = 'xls';
				break;
			default :
				$extension = $filetype['ext'];
				break;
		}

		$data[$a->ID] = (object) array(
			'ID' => $a->ID,
			'post_media_url' => wp_get_attachment_url($a->ID),
			'post_content' => $a->post_content, 
			'post_title' => $a->post_title,
			'post_excerpt' => $a->post_excerpt,
			'post_mime_type' => $a->post_mime_type,
			'post_date' => $a->post_date,
			'post_downloads' => (int) get_post_meta( $a->ID, '_acf_mc_downloads', true ),
			'post_filesize' => size_format( filesize( get_attached_file( $a->ID ) ), 0 ),
		    'post_icon_url' => ( file_exists( plugin_dir_path( __FILE__ ) . '/assets/images/ext/' . $extension . '.gif' ) ? plugins_url( '', __FILE__ ) . '/assets/images/ext/' . $extension . '.gif' : plugins_url( '', __FILE__ ) . '/assets/images/ext/unknown.gif' )
		);
	}

	return $data;
}

/*
 * Create the shortcode to output a table of file attachments
 * @param array $args - the arguments for the shortcode.  This variable accespts the following
 * 					string|required $field_name - Which ACF field name should be used
 * 					string $container_id - Wrap the output with your custom CSS ID
 * 					string $container_class - Wrap the output with your custom CSS class
 * 					string $skin - Do you want default CSS styling to apply. yes|no
 * 					string $format - html format for the output. table|list
 * 					string $title - a title in an container heading (H3 by default).  Leave blank for no heading tag.
 *                  string $title_container - the html container to wrap the title in (H3 by default).
 * 					string $show_meta - Do you want to display file metadata (size, downloads, date)? yes|no
 * @param int $post_id - the id of the post the field is attached to - if not specified, uses post id for current page
 * 
 * return string $html
 */
function acf_mc_do_shortcodes($args, $post_id = null){
	global $wp;
	
	$post_id = $post_id > 0 ? $post_id : get_the_ID();
	extract( shortcode_atts( array(
			'field_name' => null,
			'container_id' => null,
			'container_class' => 'null',
			'skin' => null,
			'format' => 'table',
			'title' => null,
            'title_container' => 'h3',
			'show_meta' => 'yes'
	), $args ) );
	
	if(!$field_name) {
		return false;
	}
	
	if($skin == 'yes') {
		$container_class .= ' acf-mc-sc-output';
	}
	
	$plugin_dir = plugins_url( '', __FILE__ );
	
	//get the data
	$response = acf_media_cluster($field_name, $post_id);
	
	$html = '';
	
	if( !empty($response) and count($response) > 0 ){
	
		if($title) {
			$html .= '<'.$title_container.' class="download-title">'.$title.'</'.$title_container.'>';
		}
		
		if($format == 'table') { //format as table
			$html .= '<div class="' . $container_class . '" id="' . $container_id . '">';
			$html .= '<table>';
			$html .= '<thead class="acf-mc-sc-output-row">';
			$html .= '<th class="acf-mc-sc-output-title">File</th>';
			if($show_meta == 'yes') {
				$html .= '<th class="acf-mc-sc-output-date">Upload Date</th>';
				$html .= '<th class="acf-mc-sc-output-size">Filesize</th>';
				$html .= '<th class="acf-mc-sc-output-download-count">Downloads</th>';
			}
			$html .= '</thead>';
			foreach($response as $item){
				$html .= '<tr class="acf-mc-sc-output-row">';
				$html .= '<td class="acf-mc-sc-output-title">';
				$html .= '<img src="'.$item->post_icon_url.'" role="presentation" alt="Download Icon" /> ';
				$html .= '<a target="_blank" aria-label="'.$item->post_title.' File Download" href="' . home_url( $wp->request ) .'?acf_mc_download=1&acf_mc_id=' . $item->ID . '&acf_mc_field_name='.$field_name.'">' . $item->post_title . '</a>';
				$html .= '</td>';
				if($show_meta == 'yes') {
					$html .= '<td class="acf-mc-sc-output-date">' . date('Y-m-d', strtotime($item->post_date)) . '</td>';
					$html .= '<td class="acf-mc-sc-output-size">' . $item->post_filesize . '</td>';
					$html .= '<td class="acf-mc-sc-output-downloads">' . $item->post_downloads . '</td>';
				}
				$html .= '</tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		else { //format as list
			$html .= '<div class="' . $container_class . '" id="' . $container_id . '">';
			$html .= '<ul>';
			foreach($response as $item){				
				$html .= '<li>';
				$html .= '<img src="'.$item->post_icon_url.'" role="presentation" alt="Download Icon" /> <a target="_blank" aria-label="'.$item->post_title.' File Download" href="' . home_url( $wp->request ) .'?acf_mc_download=1&acf_mc_id=' . $item->ID . '&acf_mc_field_name='.$field_name.'">' . $item->post_title . '</a>';
				if($show_meta == 'yes') {
					$html .= '<br /><span class="small">File Size: '.$item->post_filesize;
					$html .= ' | Uploaded: ' . date('Y-m-d', strtotime($item->post_date));
					$html .= ' | Downloaded ' . $item->post_downloads . ' times</span>';
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
		
		if( $skin == "yes" ){
			add_action('wp_footer', 'acf_mc_sc_scripts');
		}
	}
	
	return $html;
}

/*
 * Register all shortcodes
 */
function acf_mc_register_shortcodes(){
	add_shortcode('acf-media-cluster', 'acf_mc_do_shortcodes');
}
add_action( 'init', 'acf_mc_register_shortcodes');

/*
 * Output additional scripts and styles for the shortcode
 */
function acf_mc_sc_scripts(){
?>
	<style type="text/css">
		.acf-mc-sc-output{
			margin-left: auto;
			width: 1170px;
			margin-right: auto;
		}
		.acf-mc-sc-output table{
			width: 100%;
			border-spacing: 0;
			border-collapse: collapse;
		}
		.acf-mc-sc-output table th{
			background: #f5f5f5;
		}
		.acf-mc-sc-output table th,
		.acf-mc-sc-output table tr td{
			border: 1px solid #ddd;
			padding: 8px;
			line-height: 1.42857143;
			text-align: left;
			vertical-align: top;
			border-top: 1px solid #ddd;
		}
		.acf-mc-sc-output table .acf-mc-sc-output-title,
		.acf-mc-sc-output table .acf-mc-sc-output-caption{
			width: 40%;
		}
		.acf-mc-sc-output table td.acf-mc-sc-output-download a{
			color: #fff;
			background-color: #337ab7;
			border-color: #2e6da4;
			padding: 6px 12px;
			text-decoration: none;
		}
		.acf-mc-sc-output table td.acf-mc-sc-output-download a:hover{
			color: #fff;
			background-color: #286090;
			border-color: #204d74;
		}
		.acf-mc-sc-output table td.acf-mc-sc-output-download{
			width: 1%;
		}
	</style>
<?php
}
 
/*
 * Get the field's option settings
 * 
 * @param string $field_name - the name of the field
 * return array $settings
 */
function acf_mc_get_field_settings($field_name = null) {
	if(!$field_name) {
		return false;
	}
	
	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_excerpt = '$field_name' AND post_type = 'acf-field' LIMIT 1", OBJECT );

	if(!$results) {
		return false;
	}

	$settings = unserialize($results[0]->post_content);

	return $settings;
}
 
 /**
  * Process attachment download function - borrowed from the Download Attachments plugin
  *
  * @param int $attachment_id - the ID of the attachemnt
  * @param string $field_name - the name of the field (optional, but is needed to retrieve field settings)
  * @return mixed
  */
function acf_mc_download_attachment( $attachment_id = 0, $field_name = null ) {

	if ( get_post_type( $attachment_id ) === 'attachment' ) {
		// get options - we're using an array in case we want to add additional settings in the future
		
		if($field_name) {
			$options = acf_mc_get_field_settings($field_name);
		}
		else {
			$options = array();
		}
		
		//if, for some reason we can't find the force download setting, default to no
		if ( ! isset( $options['acf_mc_force_download'] ) ) {
			$options['acf_mc_force_download'] = 'No';
		}

		// get wp upload directory data
		$uploads = wp_upload_dir();

		// get file name
		$attachment = get_post_meta( $attachment_id, '_wp_attached_file', true );

		// get downloads count
		$downloads_count = (int) get_post_meta( $attachment_id, '_acf_mc_downloads', true );

		// force download
		if ( $options['acf_mc_force_download'] === 'Yes' ) {
			// get file path
			$filepath = apply_filters( 'acf_mc_download_attachment_filepath', $uploads['basedir'] . '/' . $attachment, $attachment_id );

			// file exists?
			if ( ! file_exists( $filepath ) || ! is_readable( $filepath ) ) {
				return false;
			}
			// if filename contains folders
			if ( ( $position = strrpos( $attachment, '/', 0 ) ) !== false ) {
				$filename = substr( $attachment, $position + 1 );
			}
			else {
				$filename = $attachment;
			}

			//check and temporarily set download requirements if the server allows it
			acf_mc_settings_helper();

			// set needed headers
			nocache_headers();
			header( 'Robots: none' );
			header( 'Content-Type: application/download' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . rawurldecode( $filename ) );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Accept-Ranges: bytes' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $filepath ) );

			// increase downloads count
			update_post_meta( $attachment_id, '_acf_mc_downloads', $downloads_count + 1, $downloads_count );

			// action hook
			do_action( 'acf_mc_process_file_download', $attachment_id );

			// start printing file
			$chunksize = 1024 * 1024;
			$handle = @fopen($filepath, 'r');
			
			if (false === $handle) {
			    return false;
			}
			
			$output_resource = fopen( 'php://output', 'w' );
			
			while (!@feof($handle)) {
			    $content  = @fread($handle, $chunksize);
			    fwrite( $output_resource, $content );
			    
			    if (ob_get_length()) {
			        ob_flush();
			        flush();
			    }
			}
			return @fclose($handle); // return the file
		} else { //non-forced downloads (obey brower file handling)
			// increase downloads count
			update_post_meta( $attachment_id, '_acf_mc_downloads', $downloads_count + 1, $downloads_count );

			// action hook
			do_action( 'acf_mc_process_file_download', $attachment_id );

			// force file url
			header( 'Location: ' . apply_filters( 'acf_mc_download_attachment_filepath', $uploads['baseurl'] . '/' . $attachment, $attachment_id ) );
			exit;
		}
	} else { // not an attachment
		return false;
	}
	
}

/**
 * Temporarily alters some server settings to allow downloads when the "Force Download" option is selected. These revert back when download completes.
 * @see acf_mc_download_attachment()
 * @return boolean
 */
function acf_mc_settings_helper() {
    //first, check to see what the server will not allow us to do
    $not_allowed = explode( ',', ini_get( 'disable_functions' ) );
    
    // disable compression
    if(!in_array( 'ini_set', $not_allowed )) {
        if ( ini_get( 'zlib.output_compression' ) ) {
            @ini_set( 'zlib.output_compression', 0 );
        }
    }
    
    if(!in_array( 'apache_setenv', $not_allowed )) {
        if ( function_exists( 'apache_setenv' ) ) {
            @apache_setenv( 'no-gzip', 1 );
        }
    }
    
    // set max execution time limit to 45 seconds, as large files may take some extra time to process
    if ( !in_array( 'set_time_limit', $not_allowed ) && !ini_get( 'safe_mode' ) ) {
        @set_time_limit( 45 );
    }
    
    // disable magic quotes runtime (this is just a failsafe for people running an extremely outdated version of PHP)
    if(!in_array( 'set_magic_quotes_runtime', $not_allowed )) {
        if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() && version_compare( phpversion(), '5.4', '<' ) ) {
            set_magic_quotes_runtime( 0 );
        }
    }
    
    return true;
}

/**
 * Sanitization function that will take the incoming array of input, and sanitize that input before handing it back to WordPress for further processing.
 *
 * @param array $input_array - The address input.
 * @return array $new_input - The sanitized input.
 */
 function acf_mc_sanitize_array_helper( $input_array ) {
    $new_input = array();
    
    // Loop through the input array and sanitize each of the keys and values
    foreach ($input_array as $key => &$val) {
        
        if( !is_array($val) ) {
            // if $val is not an array, just sanitize the values of $key and $val and add them to the new array
            $new_input[ sanitize_text_field($key) ] = sanitize_text_field( $val );
        }
        else { //deal with multi-dimensional arrays
            // if $val is an array, sanitize the key, and then go back into the function to sanitize $val before adding to the new array
            $val = acf_mc_sanitize_array_helper($val);
            $new_input[ sanitize_text_field($key) ] = $val;
        }
                
    }
    return $new_input;
}