(function($){
	
	
	/**
	*  initialize_field
	*
	*  This function will initialize the $field.
	*
	*  @date	04/06/22
	*  @since	5.12.2
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize_field( $field ) {
		
		//$field.doStuff();
		
	}
	
	
	if( typeof acf.add_action !== 'undefined' ) {
	
		/*
		*  ready & append (ACF5)
		*
		*  These two events are called when a field element is ready for initizliation.
		*  - ready: on page load similar to $(document).ready()
		*  - append: on new DOM elements appended via repeater field or other AJAX calls
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		acf.add_action('ready_field/type=acf_nedia_cluster', initialize_field);
		acf.add_action('append_field/type=acf_nedia_cluster', initialize_field);
		
		
	} else {
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  These single event is called when a field element is ready for initizliation.
		*
		*  @param	event		an event object. This can be ignored
		*  @param	element		An element which contains the new HTML
		*  @return	n/a
		*/
		
		$(document).on('acf/setup_fields', function(e, postbox){
			
			// find all relevant fields
			$(postbox).find('.field[data-field_type="acf_nedia_cluster"]').each(function(){
				
				// initialize
				initialize_field( $(this) );
				
			});
		
		});
	
	}

	//choose a file from the media library to add to the list
	$(document).on('click', '.button-choose-file', function(){
		var media, key, name, group;
		key = $(this).attr('data-key');
		group = $(this).attr('data-group');
		name = $(this).attr('data-name');
		console.log(name);
		if (media){
			media.open(); return;
		}
		media = wp.media.frames.file_frame = wp.media({
			title: 'Choose',
			button: {
			text: 'Choose'
		}, multiple: false });
		media.on('select', function() {
			attachment = media.state().get('selection').first().toJSON();
			//$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-filename .acf-mc-field-file-viewer').attr('href', attachment.url);
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-id').html(attachment.id);
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-filename input[name="filename"]').val(attachment.filename);
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-title input[name="title"]').val(attachment.title);
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-title').html('<a id="title-link-' + attachment.id + '" href="' + attachment.url + '" target="_blank">' + attachment.title + '</a>');
			//$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action').prepend('<input class="attachmentid" type="hidden" name="acf-mc-fields[' + name + '][]" value="' + attachment.id + '"/>');
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action .button-edit').removeClass("acf-mc-field-hide");
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action .button-edit').attr("data-attachment_id",attachment.id);
			$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action .button-delete').removeClass("acf-mc-field-hide");
			
			//in case we change our mind about a file and change it before saving, we need to update the attachment id in the hidden field
			if ($(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action').find('input.attachmentid').length === 0) {
			    // code to run if no file has been selected yet
			    $(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action').prepend('<input class="attachmentid" type="hidden" name="acf-mc-fields[' + name + '][]" value="' + attachment.id + '"/>');
			}
			else {
				// code to run if we're updating the row with a different file
			    $(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action').find('input.attachmentid').val(attachment.id);
			}
		});
		media.open();
	});

	//add a new row
	$(document).on('click', '.acf-mc-field-group-row .button-plus', function(){
		var key = $(this).attr('data-key');
		var group = $(this).attr('data-group');
		var groupIndex = $(".acf-mc-" + key + ' .acf-mc-field-group-row').length + 1;
		var name = $(this).attr('data-name');
		$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group + ' .acf-mc-field-column-action .button-plus').hide();
		$.get(ajaxurl + '?action=acf_mc_cluster_field_group&noajax=true&fname=' + name + '&key=' + key + '&group=' + groupIndex, function(data){
			$(".acf-mc-" + key + " .acf-mc-field-group-container").append(data);
		});
		return false;
	});

	//remove the row when the delete button is clicked
	$(document).on('click', '.acf-mc-field-group-row .button-delete', function(){
		var key = $(this).attr('data-key');
		var group = $(this).attr('data-group');
		$(".acf-mc-" + key + " .acf-mc-field-group-container ." + group).remove();
		return false;
	});

	//open the modal window when the edit button is clicked
	$(document).on('click', '.acf-mc-field-group-row .button-edit', function(){
		var key, name, post_id;
		key = $(this).attr('data-key');
		name = $(this).attr('data-name');
		post_id = $(this).attr('data-post_id');
		attachment_id = $(this).attr('data-attachment_id');
		$('body').append('<div class="acf-mc-backdrop"></div>');
		$('body').append('<div class="acf-mc-modal-cotaniner"><div class="acf-mc-modal-cotaniner-loading">Loading...</div></div>');
		$.get(ajaxurl + "?action=acf_mc_cluster_edit_fields&post_id=" + post_id + "&attachment_id=" + attachment_id + "&acf-mc-key=" + key + "&acf-mc-name=" + name, function(data){
			$('.acf-mc-modal-cotaniner').html(data);
		});
		return false;
	});

	//close the modal window
	$(document).on('click', '.acf-mc_modal-close', function(){
		$(".acf-mc-backdrop").remove();
		$(".acf-mc-modal-cotaniner").remove();
		return false;
	});

	//save the data from the edit form
	$(document).on('submit', '.acf-mc-modal-cotaniner .acf-mc-modal-cotaniner-content form', function(){
		var form = $(this).serializeArray();
		var button = $('.acf-mc-modal-cotaniner .acf-mc-modal-cotaniner-content form .button-primary');
		button.html("Saving").attr("disabled", true);
		$.post(ajaxurl, form, function(data){
			//update the title text field
			var post_id = form[1].value;
			$('#title-'+post_id).val(form[7].value);
			$('#title-link-'+post_id).text(form[7].value);
			
			//close the modal window
			button.html("Save Changes").attr("disabled", false);
			$(".acf-mc-backdrop").remove();
			$(".acf-mc-modal-cotaniner").remove();
		});
		return false;
	});
	
	//make file lists in the rendered field sortable
	$(document).ready(function( $ ) {
		$( ".acf-mc-sortable" ).sortable({
          delay: 150
        });
	});

})(jQuery);
