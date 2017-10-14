function openCity(evt, cityName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the link that opened the tab
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

jQuery(document).ready(function($){

  var mediaUploader;
  var mediaUploader_2;

  $('#upload-button').click(function(e) {
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function() {
      attachment = mediaUploader.state().get('selection').first().toJSON();
      $('#image_admin_login_logo').attr('src', attachment.url);
	  $('#url_admin_login_logo').val(attachment.url);
    });
    // Open the uploader dialog
    mediaUploader.open();
  });
  
	$('#reset_logo_upload').click(function() {
		$('#image_admin_login_logo').attr('src', '');
		$('#url_admin_login_logo').val('');
	});
	
	$('#upload-button-small-logo').click(function(e) {
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader_2) {
      mediaUploader_2.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader_2 = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader_2.on('select', function() {
      attachment = mediaUploader_2.state().get('selection').first().toJSON();
      $('#image_admin_small_logo').attr('src', attachment.url);
	  $('#url_admin_small_logo').val(attachment.url);
    });
    // Open the uploader dialog
    mediaUploader_2.open();
  });
  
	$('#reset_small_logo_upload').click(function() {
		$('#image_admin_small_logo').attr('src', '');
		$('#url_admin_small_logo').val('');
	});

});

jQuery(document).ready(function($){
    $checks = $('.options_for_top_bar_menu');
    $checks.on('change', function() {
        var string = $checks.filter(":checked").map(function(i,v){
            return this.value;
        }).get().join(" ");
        $('#options_for_top_bar_menus').val(string);
    });
	
	$menu_check = $('.options_for_menu_item_checkbox');
	$menu_check.on('change', function() {
        var string = $menu_check.filter(":checked").map(function(i,v){
            return this.value;
        }).get().join(" ");
        $('#menu_items_list_to_hide').val(string);
    });
	
	
	$submenu_check = $('.options_for_submenu_item_checkbox');
	$submenu_check.on('change', function() {
        var string = $submenu_check.filter(":checked").map(function(i,v){
            return this.value;
        }).get().join(" ");
        $('#submenu_items_list_to_hide').val(string);
    });
	
	$plugin_check = $('.options_plugin_items_list_to_hide');
	$plugin_check.on('change', function() {
        var string = $plugin_check.filter(":checked").map(function(i,v){
            return this.value;
        }).get().join(" ");
        $('#plugin_items_list_to_hide').val(string);
    });
	
});