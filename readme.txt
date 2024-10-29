=== ACF My Media Cluster ===
Contributors: kionae
Tags: acf, media, pdf, documents, download-files, downloads
Requires at least: 3.6.0
Tested up to: 6.6
Stable tag: 1.2.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ACF My Media Cluster is an extension for the Advance Custom Fields plugin, which adds the ability to create groups of media files for download on a page/post/custom post type. Based on an add-on created by Navneil Naicker and Download Attachments by dFactory.

== Description ==

ACF My Media Cluster is an extension for Advance Custom Fields which adds the feature to create groups of media files for download on a page/post/custom post type. The plugin does come with both a simple to use shortcode and a helper function if you wish to customize your output.
* Visually create your Fields in the ACF interface.
* Assign your fields to post types
* Easily load data through a simple and friendly helper function, or just use the shortcode.
* Uses the native WordPress custom post type for ease of use and fast processing
* Uses the native WordPress metadata for ease of use and fast processing
* Add multiple media files to your posts, pages, and custom post types. You can also modify title, caption and description from this interface.

= Usage =
Use the helper function below to pull data from the database. The function will be return an array. The helper function takes in 3 parameters.

`acf_media_cluster(string|required $acf_field_name, int $postID, array $options);`

= Example =
Based on the helper function above. Let say we want to pull annual reports from current page.

`acf_media_cluster('annual_reports', get_the_ID());`

The data that will be return will be an array. You can then loop over the array and use the data anyway you want.

`$ap = acf_media_cluster('annual_reports', get_the_ID());
if( !empty($ap) ){
    foreach($ap as $item){
        var_dump($item); //Use the data as you wish
    }
}`

= Options =
The 3rd parameter of the `acf_media_cluster(string|required $acf_field_name, int $postID, array $options);` helper function is options which takes in an array. You can pass the following:

`acf_media_cluster('annual_reports', get_the_ID(), array(
    'orderby' => 'post__in',
    'order' => 'ASC'
));`

For acceptable values for order and orderby, please refer to <https://developer.wordpress.org/reference/functions/get_posts/>

= Shortcode =
In your editor, add the following shortcode where you want the media to appear.

`[acf-media-cluster field_name="my_media_field"]`

The shortcode accepts the following parameters.

string|required $field_name - Which ACF field name should be used
string $container_id - Wrap the output with your custom CSS ID name
string $container_class - Wrap the output with your custom CSS class name
string $skin - Do you want default CSS styling to apply? yes|no
string $format - html format for the output. table|list
string $title - a title wrapped in an container tag.  Leave blank for no tag.
string $title_container - html tag to wrap the title in, with no brackets. "h3" is default.
string $show_meta - Do you want to display file metadata (size, downloads, date)? yes|no

= Compatibility =

This ACF field type is compatible with:
* ACF 6
* ACF 5

This ACF field is compatible with the iThemes Security plugin, but you must uncheck the option for "Disable PHP in plugins" under PHP Execution in System Tweaks.

= Credits =
This plugin is based on ACF Media Cluster by Navneil Naicker. (<https://wordpress.org/plugins/acf-media-cluster/>)

It also takes some inspiration from the Download Attachments plugin by dfactory, which sadly no longer exists.

== Installation ==

1. Copy the `acf-media-cluster` folder into your `wp-content/plugins` folder
2. Activate the ACF My Media Cluster plugin via the plugins admin page
3. Create a new field via ACF and select the Media Cluster type
4. Read the description above for usage instructions

== Changelog ==

= 1.2.10 =
* Fixed a javascript issue that prevented the user from updating the selected file before saving in some circumstances.
* Fixed a javascript issue that appended the name of a file to existing text instead of overwriting when updating the selected file in some circumstances.
* Now dynamically selecting the plugin version number from file headers instead of hardcoding it.

= 1.2.9 =
* Additional security and sanitization of data.

= 1.2.8 =
* Additional security and sanitization of data.

= 1.2.7 =
* Additional security and sanitization of data.

= 1.2.6 =
* Additional security and sanitization of data.
* Changes to the way files are referenced
* Fixed an output bug in the shortcode's list option


= 1.2.5 =
* Additional security and sanitization of data.
* Fixed a PHP warning on the field creation page.

= 1.2.4=
* Added better security and sanitization of data
* Removed the download.php file to eliminate external calls to that script
* Moved inline javascript to the acf-media-cluster.js file

= 1.2.3 =
* Re-added backwards compatibility with ACF version 5, and cleaned things up to prepare for future ACF updates.
* Added more documentation to codebase.
* Rebranded for public release.

= 1.2.2 =
* Finally fixed the issue with the button clicks requiring a perfectly still mouse due to the sorting function.
* Fixed the model edit box on files added to the list but not yet saved.

= 1.2.1 =
* Fixed issue with clusters not rendering more than 5 media links in the backend

= 1.2.0 =
* Made custom field settings compatible with ACF 6.x

= 1.1.4 =
* Added ability to change the wrapper tag for the file list title.

= 1.1.3 =
* Added image alts to download link icons for accessibility

= 1.1.2 =
* Added aria-label and role attributes to download links for accessibility

= 1.1.1 =
* Fixed a jQuery conflict with ACF's date picker field type

= 1.1.0 =
* Fixed primary data retrieval function
* Fixed shortcode output
* Added ability to reorder files
* Title field now updates in backend via jQuery on edit
* Added filetype icons
* Added download count
* Added format and content options to the shortcode
* Added meta info on the file to the post edit page

= 1.0.0 =
* Initial Release.