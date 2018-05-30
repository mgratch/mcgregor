<?php
/**
 * Plugin Name: Admin Tools
 * Plugin URI: http://www.madadim.co.il
 * Description: Preparing your customer management interface easily
 * Version: 1.3.7
 * Author: Yehi Co
 * Author URI: http://www.madadim.co.il
 * License: GPL2
 * Text Domain: admin-tools


Admin Tools is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Admin Tools is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Admin Tools. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

define( 'YC_ADMIN_TOOLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YC_ADMIN_TOOLS_PLUGIN_URL', plugin_basename( __FILE__ ) );

/*-------------------class start------------------*/

class YC_AdminTools {

function __construct() {

	add_action( 'admin_menu', array($this, 'ycat_admin_menu') );
	add_action( 'admin_init', array($this, 'ycat_register_setting') );
	add_action( 'admin_enqueue_scripts', array($this, 'add_scripts_and_styles') );
	add_action( 'plugins_loaded', array($this, 'ycat_load_textdomain') );
	add_action( 'pre_user_query', array($this, 'admin_user_hidden_query') );
	add_action( 'pre_current_active_plugins', array($this, 'hide_admin_tools_plugin') );
	add_action( 'admin_menu', array($this, 'remove_admin_menus'), 999 );
	add_action( 'pre_current_active_plugins', array($this, 'hide_plugins') );
	add_action( 'login_enqueue_scripts', array($this, 'my_login_logo') );
	add_action( 'init', array($this, 'hide_top_bar') );
	add_action( 'admin_enqueue_scripts', array($this, 'my_small_logo') );
	add_action( 'wp_enqueue_scripts', array( $this, 'my_small_logo' ) );
	add_action( 'wp_before_admin_bar_render', array($this, 'remove_admin_bar_links') );
	add_action( 'admin_bar_menu', array($this, 'add_site_menu_to_top_bar'), 9999 );
	add_action( 'admin_bar_menu', array( $this, 'save_menu_nodes'), 9999 );
	add_action( 'admin_head', array( $this, 'hide_all_admin_notices' ), 999 );
	add_action( 'admin_init', array($this, 'disable_and_hide_updates_options') );
	add_action( 'admin_menu', array($this, 'remove_plugin_update_count') );
	// filter
	if(esc_attr( get_option('options_for_disable_dev_auto_core_updates')) == 'true' ) {
		add_filter( 'allow_dev_auto_core_updates', '__return_false' );
	}
	if(esc_attr( get_option('options_for_disable_minor_auto_core_updates')) == 'true' ) {
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );
	}
	if(esc_attr( get_option('options_for_disable_major_auto_core_updates')) == 'true' ) {
		add_filter( 'allow_major_auto_core_updates', '__return_false' );
	}
	if(esc_attr( get_option('options_for_disable_translation_files_updates')) == 'true' ) {
		add_filter( 'auto_update_translation', '__return_false' );
	}
	if(esc_attr( get_option('options_for_disable_update_notification_emails')) == 'true' ) {
		add_filter( 'auto_core_update_send_email', '__return_false' );
	}
	add_filter( 'auto_update_plugin', array( $this, 'auto_update_specific_plugins' ), 10, 2 );
	add_filter( 'plugin_action_links_' . YC_ADMIN_TOOLS_PLUGIN_URL, array( $this, 'admin_tools_action_links' ) );
}

public function ycat_admin_menu() {
	$admin_tools_hidden = esc_attr( get_option('admin_tools_hidden'));
	$user_id = get_current_user_id();
	if( $admin_tools_hidden == '' || $user_id == $admin_tools_hidden ){
		add_submenu_page('options-general.php', 'Admin Tools Settings', __( 'Admin Tools', 'admin-tools' ), 'manage_options', 'admin-tools', array( $this, 'ycat_settings_page' ) );
	}
}

function ycat_register_setting() {
	register_setting( 'admin-tools', 'admin_user_hidden' );
	register_setting( 'admin-tools', 'admin_tools_hidden' );
	register_setting( 'admin-tools', 'admin_login_logo' );
	register_setting( 'admin-tools', 'menus_only_to' );
	register_setting( 'admin-tools', 'plugins_only_to' );
	register_setting( 'admin-tools', 'menu_items_list_to_hide' );
	register_setting( 'admin-tools', 'submenu_items_list_to_hide' );
	register_setting( 'admin-tools', 'plugin_items_list_to_hide' );
	register_setting( 'admin-tools', 'top_bar_only_to' );
	register_setting( 'admin-tools', 'hide_top_bar' );
	register_setting( 'admin-tools', 'admin_small_logo' );
	register_setting( 'admin-tools', 'options_for_top_bar_menus' );
	register_setting( 'admin-tools', 'add_site_menu' );
	register_setting( 'admin-tools', 'admin_notices_only_to' );
	register_setting( 'admin-tools', 'options_for_disable_dev_auto_core_updates' );
	register_setting( 'admin-tools', 'options_for_disable_minor_auto_core_updates' );
	register_setting( 'admin-tools', 'options_for_disable_major_auto_core_updates' );
	register_setting( 'admin-tools', 'options_for_disable_translation_files_updates' );
	register_setting( 'admin-tools', 'options_for_disable_update_notification_emails' );
	register_setting( 'admin-tools', 'options_for_disable_update_admin_tools_plugin' );
	register_setting( 'admin-tools', 'options_for_disable_and_hide_wordpress_updates' );
	register_setting( 'admin-tools', 'options_for_disable_and_hide_plugins_updates' );
	register_setting( 'admin-tools', 'options_for_disable_and_hide_themes_updates' );
}

function add_scripts_and_styles() {
    $screen = get_current_screen();
    if ( $screen->id == 'settings_page_admin-tools' ) {
		$ycat_js_ver  = date("ymd-Gis", filemtime( YC_ADMIN_TOOLS_PLUGIN_DIR . 'js/ycat.js' ));
		$ycat_css_ver = date("ymd-Gis", filemtime( YC_ADMIN_TOOLS_PLUGIN_DIR . 'css/ycat.css' ));
		
		wp_enqueue_script( 'ycat-js', plugins_url( '/js/ycat.js', YC_ADMIN_TOOLS_PLUGIN_URL), array(), $ycat_js_ver );
		wp_enqueue_style( 'ycat-css', plugins_url( '/css/ycat.css', YC_ADMIN_TOOLS_PLUGIN_URL ), false, $ycat_css_ver );
		wp_enqueue_style ( 'ycat-css' );
		wp_enqueue_media();
    }
	if ( $screen->id == 'plugins' ) {
		$ycat_css_ver = date("ymd-Gis", filemtime( YC_ADMIN_TOOLS_PLUGIN_DIR . 'css/ycat.css' ));
		
		wp_enqueue_style( 'ycat-css', plugins_url( '/css/ycat.css', YC_ADMIN_TOOLS_PLUGIN_URL ), false, $ycat_css_ver );
		wp_enqueue_style ( 'ycat-css' );
    }
}

function ycat_settings_page() {
	global $menu, $submenu;
	$user = wp_get_current_user();
	$admin_tools_hidden = esc_attr( get_option('admin_tools_hidden'));
	$user_id = $user->ID;
?>

<div class="wrap">
<h2><?php _e( 'Admin Tools Settings', 'admin-tools' ) ?></h2>
<?php
if( $user_id == $admin_tools_hidden || $admin_tools_hidden == '' ) {
?>
<form id="admin_tools_form" name="admin_tools_form" method="post" action="options.php" onsubmit="return checkForm()">
<div class="ycat-settings">
<ul class="tab">
  <li><a href="#" class="tablinks active" onclick="openCity(event, 'General')"><?php _e( 'General', 'admin-tools' ) ?></a></li>
  <li><a href="#" class="tablinks" onclick="openCity(event, 'AdminMenuTab')"><?php _e( 'Admin Menu', 'admin-tools' ) ?></a></li>
  <li><a href="#" class="tablinks" onclick="openCity(event, 'Plugins')"><?php _e( 'Plugins', 'admin-tools' ) ?></a></li>
  <li><a href="#" class="tablinks" onclick="openCity(event, 'TopBar')"><?php _e( 'Top Bar', 'admin-tools' ) ?></a></li>
  <li><a href="#" class="tablinks" onclick="openCity(event, 'Advanced')"><?php _e( 'Advanced', 'admin-tools' ) ?></a></li>
</ul>
<div id="General" class="tabcontent" style="display: block;">
	<h2><?php _e('General', 'admin-tools')?></h2>
	<?php
	settings_fields( 'admin-tools' );
	do_settings_sections( 'admin-tools' );
	?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	    <tbody>
	    <tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="admin_user_hidden"><?php _e('Admin user hidden', 'admin-tools')?></label>
	            <p><?php _e('Hide Your user from other users', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="admin_user_hidden" type="checkbox" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('admin_user_hidden')) == $user_id ) {echo 'checked="checked"';} ?>><label for="hide_user_id"><?php _e('Hide user ID:', 'admin-tools') ?> <?php echo $user_id; ?></label>
	        </td>
	    </tr>
		<?php if ( is_super_admin() ) { ?>
	    <tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="hide_admin_tools"><?php _e('Hide admin tools plugin', 'admin-tools')?></label>
	            <p><?php _e('Hide the plugin from other users. Only you can see the plugin', 'admin-tools')?> (ID: <?php echo $user_id; ?>)</p>
	        </th>
	        <td>
				<input name="admin_tools_hidden" type="checkbox" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('admin_tools_hidden')) == $user_id ) {echo 'checked="checked"';} ?>><label for="hide_admin_tools_checkbox"><?php _e('Hide this plugin', 'admin-tools') ?></label>
	        </td>
	    </tr>
		<?php } ?>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="change_login_logo"><?php _e('Change login logo', 'admin-tools')?></label>
	            <p><?php _e('Choose your brand logo', 'admin-tools')?></p>
	        </th>
	        <td>
				<input id="url_admin_login_logo" name="admin_login_logo" type="text" value="<?php echo get_option('admin_login_logo'); ?>" hidden />
				<img id="image_admin_login_logo" src="<?php echo get_option('admin_login_logo'); ?>" alt="" target="_blank" rel="external" style="max-width: 200px;"><br>
				<input id="upload-button" type="button" class="button" value="<?php _e('Upload Image', 'admin-tools')?>" /> <span class="button remove-image" id="reset_logo_upload" rel="logo_upload"><?php _e('Remove', 'admin-tools')?></span>
	        </td>
	    </tr>
	    </tbody>
	</table>
</div>

<div id="AdminMenuTab" class="tabcontent">
  <h2><?php _e('Admin Menu', 'admin-tools')?></h2>
	<?php
	settings_fields( 'admin-tools' );
	do_settings_sections( 'admin-tools' );
	?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	    <tbody>
	    <tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="menus_only_admins_or_me"><?php _e('Who can see', 'admin-tools')?></label>
	            <p><?php _e('Hide menus from who is not administrator or hide from everyone except me', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="menus_only_to" value="" <?php  if(esc_attr( get_option('menus_only_to')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="menus_only_to" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('menus_only_to')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="menu_hidden"><?php _e('Menu hidden', 'admin-tools')?></label>
	            <p><?php _e('Hide menu items from other users', 'admin-tools')?></p>
	        </th>
	        <td>
				<?php
				foreach($menu as $menu_item){
					$menu_name = $menu_item[0];
					$menu_name = sanitize_text_field( $menu_name );
					$menu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
					$menu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
					$menu_slug = $menu_item[2];
					$menu_slug = sanitize_text_field( $menu_slug );
					$menu_slug_ns = str_replace(array("&", "?"), '', $menu_slug) . '_item';
					
					$checkboxs_menu_items = esc_attr( get_option('menu_items_list_to_hide') );
					$check_menu_item = strpos($checkboxs_menu_items, $menu_slug_ns);
					
					if($menu_name != ''){
						echo '<div class="menu-row">';
						if ($check_menu_item === false) {
							?>
							<input class="options_for_menu_item_checkbox" name="options_for_menu_item_checkbox" type="checkbox" value="<?php echo $menu_slug_ns; ?>" ><label for="hide_menu_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $menu_name; ?></label></br>
							<?php
						} else {
							?>
							<input class="options_for_menu_item_checkbox" name="options_for_menu_item_checkbox" type="checkbox" value="<?php echo $menu_slug_ns; ?>" checked> ><label for="hide_menu_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $menu_name; ?></label></br>
							<?php
						}
						foreach ($submenu as $key => $value) {
							if($key == $menu_slug ) {
								if($key != ''){
									echo '<div class="submenu-row">';
									foreach ($value as $key => $value) {
										$submenu_name = $value[0];
										$submenu_name = sanitize_text_field( $submenu_name );
										$submenu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
										$submenu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
										$submenu_slug = $value[2];
										$submenu_slug = sanitize_text_field( $submenu_slug );
										$submenu_slug_ns = str_replace(array("&", "?"), '', $submenu_slug) . '_item';
										
										$checkboxs_submenu_items = esc_attr( get_option('submenu_items_list_to_hide') );
										$check_submenu_item = strpos($checkboxs_submenu_items, $submenu_slug_ns);
										
										if ($check_submenu_item === false) {
											?>
											<input class="options_for_submenu_item_checkbox" name="options_for_submenu_item_checkbox" type="checkbox" value="<?php echo $submenu_name_ns; ?>_<?php echo $submenu_slug_ns; ?>" ><label for="hide_menu_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $submenu_name; ?></label> 
											<?php
										} else {
											?>
											<input class="options_for_submenu_item_checkbox" name="options_for_submenu_item_checkbox" type="checkbox" value="<?php echo $submenu_name_ns; ?>_<?php echo $submenu_slug_ns; ?>" checked ><label for="hide_menu_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $submenu_name; ?></label> 
											<?php
										}
									}
									echo '</div>';
								}
							}
						}
						echo '</div>';
					}
				}
				?>
				<input id="menu_items_list_to_hide" name="menu_items_list_to_hide" type="text" value="<?php echo esc_attr( get_option('menu_items_list_to_hide') ) ?>" hidden >
				<input id="submenu_items_list_to_hide" name="submenu_items_list_to_hide" type="text" value="<?php echo esc_attr( get_option('submenu_items_list_to_hide') ) ?>" hidden >
	        </td>
	    </tr>
	    </tbody>
	</table>
</div>

<div id="Plugins" class="tabcontent">
  <h2><?php _e('Plugins', 'admin-tools')?></h2>
	<?php
	settings_fields( 'admin-tools' );
	do_settings_sections( 'admin-tools' );
	?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	    <tbody>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="plugins_only_admins_or_me"><?php _e('Who can see', 'admin-tools')?></label>
	            <p><?php _e('Hide plugins from who is not administrator or hide from everyone except me', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="plugins_only_to" value="" <?php  if(esc_attr( get_option('plugins_only_to')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="plugins_only_to" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('plugins_only_to')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
	    <tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="plugin_hidden"><?php _e('Plugin hidden', 'admin-tools')?></label>
	            <p><?php _e('Hide plugin from other users', 'admin-tools')?></p>
	        </th>
	        <td>
				<?php
					$all_plugins = get_plugins();
					foreach($all_plugins as $key => $value){
						$plugin_url = $key;
						$plugin_name = $value['Name'];
						$plugin_name = sanitize_text_field( $plugin_name );
						$plugin_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
						$plugin_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);

						$checkboxs_plugin_items = esc_attr( get_option('plugin_items_list_to_hide') );
						$check_plugin_item = strpos($checkboxs_plugin_items, $plugin_url);

						if($plugin_name != '' && $plugin_name != 'Admin Tools'){
							if ($check_plugin_item === false) {
								?>
								<input class="options_plugin_items_list_to_hide" name="options_plugin_items_list_to_hide" type="checkbox" value="<?php echo $plugin_url; ?>" ><label for="hide_plugin_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $plugin_name; ?></label></br>
								<?php
							} else {
								?>
								<input class="options_plugin_items_list_to_hide" name="options_plugin_items_list_to_hide" type="checkbox" value="<?php echo $plugin_url; ?>" checked ><label for="hide_plugin_checkbox"><?php _e('Hide', 'admin-tools') ?> <?php echo $plugin_name; ?></label></br>
								<?php
							}
						}
					}
				?>
				<input id="plugin_items_list_to_hide" name="plugin_items_list_to_hide" type="text" value="<?php echo esc_attr( get_option('plugin_items_list_to_hide') ) ?>" hidden >
	        </td>
	    </tr>
	    </tbody>
	</table>
</div>

<div id="TopBar" class="tabcontent">
  <h2><?php _e('Top Bar', 'admin-tools')?></h2>
	<?php
	settings_fields( 'admin-tools' );
	do_settings_sections( 'admin-tools' );
	?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	    <tbody>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="top_bar_only_admins_or_me"><?php _e('Apply options on', 'admin-tools')?></label>
	            <p><?php _e('Starting the options on who they are not administrator or from everyone except me', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="top_bar_only_to" value="" <?php  if(esc_attr( get_option('top_bar_only_to')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="top_bar_only_to" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('top_bar_only_to')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="hide_top_bar"><?php _e('Top Bar hidden', 'admin-tools')?></label>
	            <p><?php _e('Hide Top Bar from other users', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="hide_top_bar" type="checkbox" value="yes" <?php  if(esc_attr( get_option('hide_top_bar')) == 'yes' ) {echo 'checked="checked"';} ?>><label for="hide_user_id"><?php _e('Hide all Top Bar', 'admin-tools') ?></label>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="change_small_logo"><?php _e('Change small wp-logo', 'admin-tools')?></label>
	            <p><?php _e('Choose your brand icon. Optimal dimensions: 32px on 32px', 'admin-tools')?></p>
	        </th>
	        <td>
				<input id="url_admin_small_logo" name="admin_small_logo" type="text" value="<?php echo get_option('admin_small_logo'); ?>" hidden />
				<img id="image_admin_small_logo" src="<?php echo get_option('admin_small_logo'); ?>" alt="" target="_blank" rel="external" style="max-width: 200px;"><br>
				<input id="upload-button-small-logo" type="button" class="button" value="<?php _e('Upload Image', 'admin-tools')?>" /> <span class="button remove-image" id="reset_small_logo_upload" rel="logo_upload"><?php _e('Remove', 'admin-tools')?></span>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="add_site_menu"><?php _e('Add site menu', 'admin-tools')?></label>
	            <p><?php _e('Choose menu to view on top bar, helping to develop the site', 'admin-tools')?></p>
	        </th>
	        <td>
				<select name="add_site_menu">
					<option value=""><?php _e('None', 'admin-tools')?></option>
					<?php
					$menus = get_terms('nav_menu');
					foreach($menus as $menu){
						?><option value="<?php echo $menu->name; ?>" <?php if(esc_attr( get_option('add_site_menu')) == $menu->name ) {echo 'selected';} ?> ><?php echo $menu->name; ?></option><?php
						//echo '<option value="' . $menu->name . '" ' . ((esc_attr( get_option('add_site_menu')) == $menu->name )?'selected':""). ' >' . $menu->name . '</option>';
					} 
					?>
				</select>
	        </td>
	    </tr>
	    <tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="plugin_hidden"><?php _e('Hide parts from the Top Bar', 'admin-tools')?></label>
	            <p><?php _e('Hide Top Bar parts from other users', 'admin-tools')?></p>
	        </th>
	        <td>
				<?php
				foreach($this->menu_nodes as $node){
					$node_id = $node->id;
					$node_parent = $node->parent;
					$node_title = sanitize_text_field( $node->title );
					
					$checkbox_value = esc_attr( get_option('options_for_top_bar_menus') );
					$check_menu = strpos($checkbox_value, $node_id);
					
					if ($node_parent == '') {
						if ($check_menu === false) {
							?>
								<input name="options_for_top_bar_menu" type="checkbox" value="<?php echo $node_id . ', ' ?>" class="options_for_top_bar_menu"><label for="top_bar_menu"><?php _e('Hide', 'admin-tools') ?> <?php echo $node_title ?> (<?php echo $node_id ?>)</label><br>
							<?php
						} else {
							?>  
								<input name="options_for_top_bar_menu" type="checkbox" value="<?php echo $node_id . ', ' ?>" class="options_for_top_bar_menu" checked><label for="top_bar_menu"><?php _e('Hide', 'admin-tools') ?> <?php echo $node_title ?> (<?php echo $node_id ?>)</label><br>
							<?php
						}
					}
				}
				?>
				<input id="options_for_top_bar_menus" name="options_for_top_bar_menus" type="text" value="<?php echo esc_attr( get_option('options_for_top_bar_menus') ) ?>" hidden >
	        </td>
	    </tr>
	    </tbody>
	</table>
</div>

<div id="Advanced" class="tabcontent">
  <h2><?php _e('Updates & Notifications', 'admin-tools')?></h2>
	<?php
	settings_fields( 'admin-tools' );
	do_settings_sections( 'admin-tools' );
	?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	    <tbody>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="all_notifications"><?php _e('Admin notifications', 'admin-tools')?></label>
	            <p><?php _e('Choose who can see admin notifications', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="admin_notices_only_to" value="" <?php  if(esc_attr( get_option('admin_notices_only_to')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('Everybody', 'admin-tools')?><br>
				<input type="radio" name="admin_notices_only_to" value="administrators" <?php  if(esc_attr( get_option('admin_notices_only_to')) == 'administrators' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="admin_notices_only_to" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('admin_notices_only_to')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="wordpress_updats"><?php _e('Who can update wordprss core', 'admin-tools')?></label>
	            <p><?php _e('Choose who allowed to update wordprss core', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="options_for_disable_and_hide_wordpress_updates" value="" <?php  if(esc_attr( get_option('options_for_disable_and_hide_wordpress_updates')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('Everybody', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_wordpress_updates" value="administrators" <?php  if(esc_attr( get_option('options_for_disable_and_hide_wordpress_updates')) == 'administrators' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_wordpress_updates" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('options_for_disable_and_hide_wordpress_updates')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="plugins_updats"><?php _e('Who can update wordprss.org plugins', 'admin-tools')?></label>
	            <p><?php _e('Choose who allowed to update plugins (works only on plugins from wordprss.org)', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="options_for_disable_and_hide_plugins_updates" value="" <?php  if(esc_attr( get_option('options_for_disable_and_hide_plugins_updates')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('Everybody', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_plugins_updates" value="administrators" <?php  if(esc_attr( get_option('options_for_disable_and_hide_plugins_updates')) == 'administrators' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_plugins_updates" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('options_for_disable_and_hide_plugins_updates')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="themes_updats"><?php _e('Who can update wordprss.org themes', 'admin-tools')?></label>
	            <p><?php _e('Choose who allowed to update themes (works only on themes from wordprss.org)', 'admin-tools')?></p>
	        </th>
	        <td>
				<input type="radio" name="options_for_disable_and_hide_themes_updates" value="" <?php  if(esc_attr( get_option('options_for_disable_and_hide_themes_updates')) == '' ) {echo 'checked="checked"';} ?> ><?php _e('Everybody', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_themes_updates" value="administrators" <?php  if(esc_attr( get_option('options_for_disable_and_hide_themes_updates')) == 'administrators' ) {echo 'checked="checked"';} ?> ><?php _e('All Administrators', 'admin-tools')?><br>
				<input type="radio" name="options_for_disable_and_hide_themes_updates" value="<?php echo $user_id; ?>" <?php  if(esc_attr( get_option('options_for_disable_and_hide_themes_updates')) == $user_id ) {echo 'checked="checked"';} ?> ><?php _e('Only me', 'admin-tools')?>  (ID:<?php echo $user_id; ?>)<br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="disable_automatic_updates"><?php _e('Disable automatic updates', 'admin-tools')?></label>
	            <p><?php _e('You can disable all automatic updates using the following options', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="options_for_disable_dev_auto_core_updates" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_dev_auto_core_updates')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_dev_auto_core_updates"><?php _e('Disable development updates', 'admin-tools') ?></label><br>
				<input name="options_for_disable_minor_auto_core_updates" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_minor_auto_core_updates')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_minor_auto_core_updates"><?php _e('Disable minor updates', 'admin-tools') ?></label><br>
				<input name="options_for_disable_major_auto_core_updates" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_major_auto_core_updates')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_major_auto_core_updates"><?php _e('Disable major updates', 'admin-tools') ?></label><br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="disable_translation_files_updates"><?php _e('Disable translation files updates', 'admin-tools')?></label>
	            <p><?php _e('You can disable translation files updates using the following options', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="options_for_disable_translation_files_updates" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_translation_files_updates')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_auto_update_translation"><?php _e('Disable auto update translation', 'admin-tools') ?></label><br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="disable_update_notification_emails"><?php _e('Disable update notification emails', 'admin-tools')?></label>
	            <p><?php _e('You can disable update notification emails using the following options', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="options_for_disable_update_notification_emails" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_update_notification_emails')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_update_send_email"><?php _e('Disable update send email', 'admin-tools') ?></label><br>
	        </td>
	    </tr>
		<tr class="form-field">
	        <th valign="top" scope="row">
	            <label for="disable_update_admin_tools_plugin"><?php _e('Disable update admin tools plugin', 'admin-tools')?></label>
	            <p><?php _e('You can disable update admin tools plugin using the following options', 'admin-tools')?></p>
	        </th>
	        <td>
				<input name="options_for_disable_update_admin_tools_plugin" type="checkbox" value="true" <?php  if(esc_attr( get_option('options_for_disable_update_admin_tools_plugin')) == 'true' ) {echo 'checked="checked"';} ?>><label for="disable_update_admin_tools_plugin"><?php _e('Disable update admin tools (not recommended)', 'admin-tools') ?></label><br>
	        </td>
	    </tr>
	    </tbody>
	</table>
</div>

</div>
<div class="ycat-sidebar">
	<div class="ycat-sidebar-box">
		<div class="ycat-sidebar-box-title">
			<h3><?php _e( 'Save All Settings', 'admin-tools' ) ?></h3>
		</div>
		<?php submit_button(); ?>
	</div>
</div>
</form>

<?php
} else {
	echo _e('You do not have permission to manage the plugin', 'admin-tools');
}
?>

</div>

<?php

}

function ycat_load_textdomain() {
    load_plugin_textdomain( 'admin-tools', false, plugin_basename( YC_ADMIN_TOOLS_PLUGIN_DIR . 'languages' ) );
}

function admin_user_hidden_query($user_search) {
	global $wpdb;
	$admin_user_hidden = esc_attr( get_option('admin_user_hidden'));
	$user_id = get_current_user_id();
	if( $admin_user_hidden != '' && $user_id != $admin_user_hidden ){
		$user_search->query_where = str_replace('WHERE 1=1',
		"WHERE 1=1 AND {$wpdb->users}.ID<>1",$user_search->query_where);
	add_filter( 'views_users', array( $this, 'remove_count_from_users_list') );
	}
}

function remove_count_from_users_list( $views ) {
	$users_roles = array();
	$avail_roles = wp_roles()->get_names();
	$result = count_users();
	$all_users = $result['total_users'] -1;
	$users_roles['all'] = '<a href="users.php?role=all">' . __( 'All' ) . ' <span class="count">(' . $all_users . ')</span></a>';
	foreach ( $result['avail_roles'] as $role => $count ) {
	        if ( $role == 'administrator') {
	        $count = $count -1;
	        }
	        if ( $role != 'none') {
	        $users_roles[$role] = '<a href="users.php?role=' . $role . '">' . _x(ucfirst($role), 'User role') . ' <span class="count">(' . $count . ')</span></a>';
	        }
	}
	$views = $users_roles;
	return $views;
}

function hide_admin_tools_plugin() {
	$admin_tools_hidden = esc_attr( get_option('admin_tools_hidden'));
	$user_id = get_current_user_id();
	if( $admin_tools_hidden != '' && $user_id != $admin_tools_hidden ){
		global $wp_list_table;
		$hidearr = array('admin-tools/admin-tools.php');
		$admin_tools_plugin = $wp_list_table->items;
		foreach ($admin_tools_plugin as $key => $val) {
			if (in_array($key,$hidearr)) {
				unset($wp_list_table->items[$key]);
			}
		}
	}
}


function remove_admin_menus() {
    global $menu, $submenu;
	$menus_only_to = esc_attr( get_option('menus_only_to'));
	$user_id = get_current_user_id();
	if(esc_attr( get_option('menus_only_to')) == '' ) {
		if ( ! is_super_admin() ) {
			foreach($menu as $menu_item){
				$menu_name = $menu_item[0];
				$menu_name = sanitize_text_field( $menu_name );
				$menu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
				$menu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
				$menu_slug = $menu_item[2];
				$menu_slug = sanitize_text_field( $menu_slug );
				$menu_slug_ns = str_replace(array("&", "?"), '', $menu_slug) . '_item';
				
				$checkboxs_menu_items = esc_attr( get_option('menu_items_list_to_hide') );
				$check_menu_item = strpos($checkboxs_menu_items, $menu_slug_ns);
				
				if( $check_menu_item !== false ) {
					remove_menu_page($menu_slug);
				}
				foreach ($submenu as $key => $value) {
					foreach ($value as $key => $value) {
						$value[2] = isset($value[2]) ? $value[2] : null;
						$submenu_name = $value[0];
						$submenu_name = sanitize_text_field( $submenu_name );
						$submenu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
						$submenu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
						$submenu_slug = $value[2];
						$submenu_slug = sanitize_text_field( $submenu_slug );
						$submenu_slug_ns = str_replace(array("&", "?"), '', $submenu_slug) . '_item';
						
						$checkboxs_submenu_items = esc_attr( get_option('submenu_items_list_to_hide') );
						$check_submenu_item = strpos($checkboxs_submenu_items, $submenu_slug_ns);
						
						if( $check_submenu_item !== false ) {
							remove_submenu_page( $menu_slug, $submenu_slug );
						}
					}
				}
			}
		}
	} else {
		if( $user_id != $menus_only_to ){
			foreach($menu as $menu_item){
				$menu_name = $menu_item[0];
				$menu_name = sanitize_text_field( $menu_name );
				$menu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
				$menu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $menu_name);
				$menu_slug = $menu_item[2];
				$menu_slug = sanitize_text_field( $menu_slug );
				$menu_slug_ns = str_replace(array("&", "?"), '', $menu_slug) . '_item';
				
				$checkboxs_menu_items = esc_attr( get_option('menu_items_list_to_hide') );
				$check_menu_item = strpos($checkboxs_menu_items, $menu_slug_ns);
				
				if( $check_menu_item !== false ) {
					remove_menu_page($menu_slug);
				}
				foreach ($submenu as $key => $value) {
					foreach ($value as $key => $value) {
						$value[2] = isset($value[2]) ? $value[2] : null;
						$submenu_name = $value[0];
						$submenu_name = sanitize_text_field( $submenu_name );
						$submenu_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
						$submenu_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $submenu_name);
						$submenu_slug = $value[2];
						$submenu_slug = sanitize_text_field( $submenu_slug );
						$submenu_slug_ns = str_replace(array("&", "?"), '', $submenu_slug) . '_item';
						
						$checkboxs_submenu_items = esc_attr( get_option('submenu_items_list_to_hide') );
						$check_submenu_item = strpos($checkboxs_submenu_items, $submenu_slug_ns);
						
						if( $check_submenu_item !== false ) {
							remove_submenu_page( $menu_slug, $submenu_slug );
						}
					}
				}
			}
		}
	}
}

function hide_plugins() {
	$all_plugins = get_plugins();
	$plugins_only_to = esc_attr( get_option('plugins_only_to'));
	$user_id = get_current_user_id();
	if(esc_attr( get_option('plugins_only_to')) == '' ) {
		if ( ! is_super_admin() ) {
			foreach($all_plugins as $key => $value){
				$plugin_url = $key;
				$plugin_name = $value[Name];
				$plugin_name = sanitize_text_field( $plugin_name );
				$plugin_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
				$plugin_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
				
				$checkboxs_plugin_items = esc_attr( get_option('plugin_items_list_to_hide') );
				$check_plugin_item = strpos($checkboxs_plugin_items, $plugin_url);
				
				if($plugin_name != ''){
					if ($check_plugin_item !== false) {
						global $wp_list_table;
						$hidearr = array($plugin_url);
						$admin_tools_plugin = $wp_list_table->items;
						foreach ($admin_tools_plugin as $key => $val) {
							if (in_array($key,$hidearr)) {
								unset($wp_list_table->items[$key]);
							}
						}
					}
				}
			}
			add_filter('views_plugins', array( $this, 'remove_count_from_plugins_list') );
		}
	} else {
		if( $user_id != $plugins_only_to ){
			foreach($all_plugins as $key => $value){
				$plugin_url = $key;
				$plugin_name = $value['Name'];
				$plugin_name = sanitize_text_field( $plugin_name );
				$plugin_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
				$plugin_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
				
				$checkboxs_plugin_items = esc_attr( get_option('plugin_items_list_to_hide') );
				$check_plugin_item = strpos($checkboxs_plugin_items, $plugin_url);
				
				if($plugin_name != ''){
					if ($check_plugin_item !== false) {
						global $wp_list_table;
						$hidearr = array($plugin_url);
						$admin_tools_plugin = $wp_list_table->items;
						foreach ($admin_tools_plugin as $key => $val) {
							if (in_array($key,$hidearr)) {
								unset($wp_list_table->items[$key]);
							}
						}
					}
				}
			}
			add_filter('views_plugins', array( $this, 'remove_count_from_plugins_list') );
		}
	}
}

function remove_count_from_plugins_list( $views ) {

	$all_plugins = get_plugins();
	$plugins_count = array();
	foreach($all_plugins as $key => $value){
		$plugin_url = $key;
		$plugin_name = $value['Name'];
		$plugin_name = sanitize_text_field( $plugin_name );
		$plugin_name = str_replace(array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
		$plugin_name_ns = str_replace(array(" ", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"), '', $plugin_name);
		$checkboxs_plugin_items = esc_attr( get_option('plugin_items_list_to_hide') );
		$check_plugin_item = strpos($checkboxs_plugin_items, $plugin_url);
		if( $check_plugin_item !== false && $key != 'admin-tools/admin-tools.php' ) {
			$plugins_count[$key] = '1';
		}
	}
	
	if(esc_attr( get_option('admin_tools_hidden')) != '' || esc_attr(get_option('options_for_plugin_AdminTools')) != '') {
		$admin_tools_count = '1';
	} else {
		$admin_tools_count = '0';
	}
	
	$counts_all_plugins = count($all_plugins);
	$count_result = count($plugins_count);
	$all_counts = $counts_all_plugins - $count_result - $admin_tools_count;
	$sum_counts = $count_result + $admin_tools_count;
	
	global $totals, $status;
	$status_links = array();
		foreach ( $totals as $type => $count ) {
			if ( !$count )
				continue;
			if ( $type == 'upgrade' || $type == 'recently_activated' || $type == 'dropins' || $type == 'mustuse' )
				break;
			$count = $count - $sum_counts;
			switch ( $type ) {
				case 'all':
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'plugins' );
					break;
				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count );
					$inactive_count = $count;
					break;
				case 'recently_activated':
					$text = _n( 'Recently Active <span class="count">(%s)</span>', 'Recently Active <span class="count">(%s)</span>', $count );
					break;
				case 'inactive':
					$inactive_count = $all_counts - $inactive_count;
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $inactive_count );
					break;
				case 'mustuse':
					$text = _n( 'Must-Use <span class="count">(%s)</span>', 'Must-Use <span class="count">(%s)</span>', $count );
					break;
				case 'dropins':
					$text = _n( 'Drop-ins <span class="count">(%s)</span>', 'Drop-ins <span class="count">(%s)</span>', $count );
					break;
				case 'upgrade':
					$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count );
					break;
			}

			if ( 'search' !== $type && $type !== 'inactive' ) {
				$status_links[$type] = sprintf( "<a href='%s' %s>%s</a>",
					add_query_arg('plugin_status', $type, 'plugins.php'),
					( $type === $status ) ? ' class="current"' : '',
					sprintf( $text, number_format_i18n( $count ) )
					);
			}
			
			if ( 'search' !== $type && $type == 'inactive' ) {
				$status_links[$type] = sprintf( "<a href='%s' %s>%s</a>",
					add_query_arg('plugin_status', $type, 'plugins.php'),
					( $type === $status ) ? ' class="current"' : '',
					sprintf( $text, number_format_i18n( $inactive_count ) )
					);
			}
		}
	$views = $status_links;
	return $views;
}

function my_login_logo() {
	
	if( get_option('admin_login_logo') != '' ) {
		?>
		<style type="text/css">
		#login h1 a, .login h1 a {
		background-image: url(<?php echo get_option('admin_login_logo'); ?>)!important;
		background-size: contain!important;
		padding-bottom: 30px;
		width: 100%!important;
		}
		</style>
		<?php
	}
}

function hide_top_bar() {
	
	$user_id = get_current_user_id();
	$top_bar_only_to = esc_attr( get_option('top_bar_only_to'));
	
	if(esc_attr( get_option('hide_top_bar')) == 'yes' ) {
		if( $top_bar_only_to == '' ) {
			if ( ! is_super_admin() ) {
				add_filter('show_admin_bar', '__return_false');
			}
		} else if( $top_bar_only_to != $user_id ) {
			add_filter('show_admin_bar', '__return_false');
		}
	}
}

function my_small_logo() {
	
	if( get_option('admin_small_logo') != '' ) {
		?>
		<style type="text/css">
		span.ab-icon {
			display: none;
		}
		#wp-admin-bar-wp-logo > .ab-item {
			background-image: url(<?php echo get_option('admin_small_logo'); ?>)!important;
			background-size: auto 32px!important;
			height: 32px!important;
			min-width: 32px!important;
			padding: 0px!important;
			margin: 0px 5px!important;
		}
		</style>
		<?php
	}
}

function remove_admin_bar_links() {
	global $wp_admin_bar;
	$user_id = get_current_user_id();
	$top_bar_only_to = esc_attr( get_option('top_bar_only_to'));
	
	if( $top_bar_only_to == '' ) {
		if ( ! is_super_admin() ) {
			foreach($this->menu_nodes as $node){
				$node_id = $node->id;
				$node_title = sanitize_text_field( $node->title );
				$checkbox_value = esc_attr( get_option('options_for_top_bar_menus'));
				$check_menu = strpos($checkbox_value, $node_id);
				if ($check_menu === false) {
					// do nothing
				} else {
					$wp_admin_bar->remove_menu($node_id);
				}
			}
		}
	} else if( $top_bar_only_to != $user_id ) {
		foreach($this->menu_nodes as $node){
			$node_id = $node->id;
			$node_title = sanitize_text_field( $node->title );
			$checkbox_value = esc_attr( get_option('options_for_top_bar_menus') );
			$check_menu = strpos($checkbox_value, $node_id);
			if ($check_menu === false) {
				// do nothing
			} else {
				$wp_admin_bar->remove_menu($node_id);
			}
		}
	}
}

function add_site_menu_to_top_bar() {
	global $wp_admin_bar;
	$menus = get_terms('nav_menu');
	foreach($menus as $menu){
		if ( esc_attr( get_option('add_site_menu')) == $menu->name && is_super_admin() ) {
			$args = array(
				'id'    => 'site_menu',
				'title' => __('Menu Site:', 'admin-tools') . ' ' . $menu->name,
			);
			$items = wp_get_nav_menu_items( $menu, $args );
			$wp_admin_bar->add_node( $args );
			
			foreach($items as $item) {
				if ( $item->menu_item_parent == 0 ) {
					$item->menu_item_parent = 'site_menu';
				}
				$args = array(
					'parent' => $item->menu_item_parent,
					'id'    => $item->ID,
					'title' => $item->title,
					'href'  => $item->url,
					'meta'  => array( 'target' => '_blank' ),
					
				);
				$wp_admin_bar->add_node( $args );
			}
		}
	}
}

function save_menu_nodes() {
	global $wp_admin_bar;
	$this->menu_nodes = $wp_admin_bar->get_nodes();
}

function hide_all_admin_notices() {
	$user_id = get_current_user_id();
	$admin_notices_only_to = esc_attr( get_option('admin_notices_only_to'));
	
	if( $admin_notices_only_to == 'administrators' ) {
		if ( ! is_super_admin() ) {
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	} else if ( $admin_notices_only_to != '' ) {
		if ( $admin_notices_only_to != $user_id ) {
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
}

function auto_update_specific_plugins ( $update, $item ) {
	if(esc_attr( get_option('options_for_disable_update_admin_tools_plugin')) != 'true' ) {
		$plugins = array ( 
			'admin-tools',
		);
		if ( in_array( $item->slug, $plugins ) ) {
			return true;
		} else {
			return $update;
		}
	}
}

function disable_and_hide_updates_options() {
	$user_id = get_current_user_id();
	$options_for_disable_and_hide_wordpress_updates = esc_attr( get_option('options_for_disable_and_hide_wordpress_updates'));
	$options_for_disable_and_hide_plugins_updates = esc_attr( get_option('options_for_disable_and_hide_plugins_updates'));
	$options_for_disable_and_hide_themes_updates = esc_attr( get_option('options_for_disable_and_hide_themes_updates'));

	if( $options_for_disable_and_hide_wordpress_updates == 'administrators' ) {
		if ( ! is_super_admin() ) {
			add_filter('pre_site_transient_update_core', array( $this, 'disable_and_hide_updates' ) );
		}
	} else if ( $options_for_disable_and_hide_wordpress_updates != '' ) {
		if ( $options_for_disable_and_hide_wordpress_updates != $user_id ) {
			add_filter('pre_site_transient_update_core', array( $this, 'disable_and_hide_updates' ) );
		}
	}
	
	if( $options_for_disable_and_hide_plugins_updates == 'administrators' ) {
		if ( ! is_super_admin() ) {
			add_filter('pre_site_transient_update_plugins', array( $this, 'disable_and_hide_updates' ) );
		}
	} else if ( $options_for_disable_and_hide_plugins_updates != '' ) {
		if ( $options_for_disable_and_hide_plugins_updates != $user_id ) {
			add_filter('pre_site_transient_update_plugins', array( $this, 'disable_and_hide_updates' ) );
		}
	}
	
	if( $options_for_disable_and_hide_themes_updates == 'administrators' ) {
		if ( ! is_super_admin() ) {
			add_filter('pre_site_transient_update_themes', array( $this, 'disable_and_hide_updates' ) );
		}
	} else if ( $options_for_disable_and_hide_themes_updates != '' ) {
		if ( $options_for_disable_and_hide_themes_updates != $user_id ) {
			add_filter('pre_site_transient_update_themes', array( $this, 'disable_and_hide_updates' ) );
		}
	}
}

function disable_and_hide_updates() {
	global $wp_version;
	return(object) array(
		'last_checked'=> time(),
		'version_checked'=> $wp_version,
		'updates' => array()
	);
}

function remove_plugin_update_count(){
	global $menu,$submenu;
	$user_id = get_current_user_id();
	$options_for_disable_and_hide_wordpress_updates = esc_attr( get_option('options_for_disable_and_hide_wordpress_updates'));
	$options_for_disable_and_hide_plugins_updates = esc_attr( get_option('options_for_disable_and_hide_plugins_updates'));
	$options_for_disable_and_hide_themes_updates = esc_attr( get_option('options_for_disable_and_hide_themes_updates'));
	
	if ( is_super_admin() ) {
		if( $options_for_disable_and_hide_wordpress_updates == 'administrators' || $options_for_disable_and_hide_plugins_updates == 'administrators' || $options_for_disable_and_hide_themes_updates == 'administrators' ) {
			
		} else if ( $options_for_disable_and_hide_wordpress_updates != '' || $options_for_disable_and_hide_plugins_updates != '' || $options_for_disable_and_hide_themes_updates != '' ) {
			if ( $options_for_disable_and_hide_wordpress_updates != $user_id || $options_for_disable_and_hide_plugins_updates != $user_id || $options_for_disable_and_hide_themes_updates != $user_id ) {
				$menu[65][0] = 'Plugins';	
				$submenu['index.php'][10][0] = 'Updates';	
			}
		}
	}
}

function admin_tools_action_links( $links ) {
	$settings = array('settings' => '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=admin-tools') ) .'">' . __( 'Settings' ) . '</a>');
	$links = array_merge($settings, $links);
	return $links;
}

}
 
$YC_AdminTools = new YC_AdminTools;