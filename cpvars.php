<?php
/*
* Plugin Name: CPvars
* Plugin URI: https://www.gieffeedizioni.it/classicpress
* Description: Vars in shortcodes 
* Version: 1.1.3
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it/classicpress
* Text Domain: cpvars
*/

if (!defined('ABSPATH')) die('-1');

// Load text domain
add_action( 'plugins_loaded', 'cpvars_load_textdomain' );
function cpvars_load_textdomain() {
	load_plugin_textdomain( 'cpvars', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

function wcmo_get_current_user_roles() {
 if( is_user_logged_in() ) {
 $user = wp_get_current_user();
 $roles = ( array ) $user->roles;
 return $roles; // This returns an array
 // Use this to return a single value
 // return $roles[0];
 } else {
 return array();
 }
}

/*
* Admin section
* add a settings link in plugins page
*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cpvars_pal' );
function cpvars_pal( $links ) {
	if ( current_user_can( get_option( 'cpvars-whocanedit' ) ) ) {
		$link = '<a href="' . admin_url( 'tools.php?page=cpvars' ) . '" title="' . __( 'Settings', 'cpvars' ) . '"><i class="dashicon dashicons-admin-generic"></i></a>';
		array_unshift( $links, $link );
		return $links;
	}
}

/*
*
* To be checked in v. 1.1.0 where PR #484 should have adder the right styling
* for the icon in the admin page.
*
*/
add_action('admin_enqueue_scripts', 'cpvars_admin_style');
function cpvars_admin_style( $hook ){
	if ( ! function_exists( 'classicpress_version' ) || version_compare( '1.1.0', classicpress_version() , '>' )  ){
	if ( 'plugins.php' == $hook ){
			wp_enqueue_style( 'cpvars_compatibility_css', plugins_url( 'css/cpvars-compatibility.css', __FILE__ ) );
		}
	}
}

/*
*  For CP1.1.0 DRAFT to add who can change cpvars value
*
*/

/*
- traduzioni
- aggiungere pagina a security
- vedi linea 138 lista utenti che possono modificare
*/

/*
* Admin section
* add a sumbenu to the tools menu
*/
add_action( 'admin_footer', 'cpvars_admin_script' );
function cpvars_admin_script( ) {
	$screen = get_current_screen(); 
	if ( 'tools_page_cpvars' == $screen->id ){
		wp_enqueue_script( 'cpvars_admin', plugins_url( 'js/cpvars-admin.js', __FILE__ ), array('jquery'), '1.0' );
		wp_localize_script( 'cpvars_admin', 'objectL10n', 
			array( 
				'save'     => __( 'Save', 'cpvars' ),
				'name'     => __( 'name', 'cpvars' ),
				'content'  => __( 'content', 'cpvars' ),
			) 
		);
	}
}

add_action( 'admin_menu', 'cpvars_create_menu' );

function cpvars_create_menu() {
	if ( current_user_can( get_option( 'cpvars-whocanedit' ) ) ) {
	   	$page=add_submenu_page( 'tools.php', 'CPvars', 'CPvars', get_option( 'cpvars-whocanedit' ),'cpvars', 'cpvars_settings_page' );
	}
}

function cpvars_settings_page() {
	if ( ! current_user_can( get_option( 'cpvars-whocanedit' ) ) || false ) {
		exit;
	}
	$xsx_cap_error = "";
	// Directly manage options
	if ( isset( $_POST["allvars"] ) || isset( $_POST["doeverywhere"] ) || isset( $_POST["cleanup"] ) || isset( $_POST["whocanedit"] )){
		check_admin_referer( 'cpvars-admin' );
		parse_str( $_POST["allvars"], $testvars );
		update_option( 'cpvars-vars', $_POST["allvars"] );
		
		if ( current_user_can('manage_options') ) {
			if ( isset( $_POST["doeverywhere"] )  ){
				update_option( 'cpvars-doeverywhere', 1 );
			} else {
				update_option( 'cpvars-doeverywhere', 0 );
			};
			if ( isset( $_POST["cleanup"] )  ){
				update_option( 'cpvars-cleanup', 1 );
			} else {
				update_option( 'cpvars-cleanup', 0 );
			};
			if ( isset( $_POST["whocanedit"] )  ){
				if ( current_user_can( $_POST["whocanedit"] ) ) {
					update_option( 'cpvars-whocanedit', preg_replace( '/[^a-z_]/', '', $_POST["whocanedit"] ) );
				} else {
					$xsx_cap_error = '<span style="color:red;">' . sprintf( __( 'You don\'t have <b>%s</b> capability.', 'cpvars' ), $_POST["whocanedit"] ) . '</span>';
				}; 
			};
		}
		
	} else {
		$coded_options = get_option( 'cpvars-vars' );
		parse_str( $coded_options, $testvars );
	};

	// text about plugin usage I prefer storing in the translations
	$header = __("HEADERTEXT" , 'cpvars' );

// Let's MOVE this in security page
	echo "<pre>";
	$users = get_users( array( 'fields' => array( 'ID' ) ) );
	$count = 0;
	$userlist = "";
	foreach($users as $user_id) {
		$meta = get_user_meta ( $user_id->ID );
		if ( user_can( $user_id->ID, get_option( 'cpvars-whocanedit' ) ) ) {
			$count++;
			$userlist .= $meta['nickname'][0] . ", ";
		};
	}
/* translators: 1 is the number of user. 2 is the list of users */
	printf(_n('%1$d user can change vars: %2$s.', '%1$d users can change vars: %2$s.', $count), $count, rtrim( $userlist, ', ' ));
	echo "</pre>";
// END: Let's MOVE this in security page

	?>
	<style>
		.form-table {
			width: auto !important;
			padding:100px;
		}
		.code {
			font-family: "Courier New", Courier, mono;
		}
	</style>
	<div class="wrap">
	<?php echo $header ?>
	<hr>
	<form method="POST" id="cpvars-form"  >
	
	<?php if ( current_user_can('manage_options') ): ?>
		<input type="checkbox" name="doeverywhere" class="doeverywhere" <?php if ( 1 == get_option( 'cpvars-doeverywhere' ) ){echo "checked='checked'";};?>> 
		<?php _e( 'Do shortcodes anywhere.', 'cpvars' )?> </input><br>
		<input type="checkbox" name="cleanup" class="cleanup" <?php if ( 1 == get_option( 'cpvars-cleanup' ) ){echo "checked='checked'";}; ?> >
		<?php _e( 'Delete plugin data at uninstall.', 'cpvars' )?></input><br>
		<?php _e( 'User capability requested to edit vars:', 'cpvars' )?>
		<input type="text" name="whocanedit" class="whocanedit" value="<?php echo get_option( 'cpvars-whocanedit' ) ; ?>">
		<?php  echo $xsx_cap_error ?>
		<hr>
	<?php endif; ?>
		<table class="form-table">
	<?php
	foreach ( $testvars as $key => $value ){
		echo '<tr valign="top" class="cpvars-keyvalue"><td ><input type="text" size="20" class="cpvars-key" value="' . $key . '" /></td>';
		echo '<td ><input type="text" size="100" class="cpvars-value" value="' . htmlspecialchars( $value ) . '" /></td><td><a class="dashicons dashicons-trash cpvars-delete"></a></td></tr>'; 
	}
	?>
		</table>
	<button type="button" class="button button-large button-primary  cpvars-add"><?php _e( 'Add', 'cpvars' ) ?></button>
		<?php wp_nonce_field( 'cpvars-admin' ); ?>
		<input type="submit" value="<?php _e( 'Saved', 'cpvars' ) ?>" id="cpvars-submit" class="button button-primary button-large" disabled>
	</form>
	</div>
	<?php 
} 

/**
* shortcode section
*/
add_shortcode('cpv', 'cpv');
function cpv( $atts, $content = null ) {
	$coded_options = get_option( 'cpvars-vars' );
	parse_str( $coded_options, $testvars );
	if ( isset( $testvars[$content] ) ){
		$prefilter_retval = $testvars[$content];
		$filtered_retval = apply_filters( 'cpvars_output', $prefilter_retval );
		return $filtered_retval;
	} elseif ( current_user_can( get_option( 'cpvars-whocanedit' ) ) ) {
		$url = admin_url( 'tools.php?page=cpvars' );
		/* translators: 1 is the var not defined. 2 is the url of the admin page */
		return sprintf ( __('%1$s is not defined. Define it <a href="%2$s">here</a>. (only you can see this message)', 'cpvars'), $content, $url );
	} else {
		return "";
	}
}

function cpv_do ( $var ){
	return cpv( '', $var );
};

/**
* do shortcodes everywhere section
*/
if ( 1 == get_option( 'cpvars-doeverywhere' ) ){
	$cpvars_shortcodeseverywhere_pryority = 10;
	$tags = [
		'single_post_title',
		'the_title',
		'widget_text',
		'widget_title',
		'bloginfo',
		'get_post_metadata'
	];
	foreach ( $tags as $tag ){
		add_filter( $tag, 'do_shortcode', $cpvars_shortcodeseverywhere_pryority );
	}
}

/**
* Add a menu to mce
*/
//Handle dynamic menu generation
foreach ( array('post.php','post-new.php') as $hook ) {
	add_action( "admin_head-$hook", 'cpvars_admin_head' );
}
function cpvars_admin_head() {
	$coded_options = get_option( 'cpvars-vars' );
	parse_str( $coded_options, $testvars );
	$cpvars_dynamic_mce = "";
	foreach ( $testvars as $var => $value){
		if ( strlen( $value ) <= 10 ){
			$example_data = $value;
		} else {
			$example_data = substr( $value, 0, 7) . "..." ;
		};
		$example_data = ' (' . $example_data . ')';
		$cpvars_dynamic_mce .= 
			'{text: "' . $var . $example_data . '",onclick: function() {tinymce.activeEditor.insertContent("[cpv]' . $var . '[/cpv]"); }},';
	};
	$cpvars_dynamic_mce = '$cpvars_dynmenu=[' . $cpvars_dynamic_mce . ']';
	?>
	<script type='text/javascript'>
		<?php echo $cpvars_dynamic_mce ?>
	</script>
	<?php
}                 

add_action('admin_head', 'cpvars_add_mce_menu');
function cpvars_add_mce_menu() {
	if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'cpvars_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'cpvars_register_mce_menu' );
	}
}

function cpvars_register_mce_menu( $buttons ) {
	array_push( $buttons, 'cpvars_mce_menu' );
	return $buttons;
}

function cpvars_add_tinymce_plugin( $plugin_array ) {
	global $useminified;
	$plugin_array['cpvars_mce_menu'] = plugins_url( 'js/cpvars-mce-menu.js', __FILE__ );
	return $plugin_array;
}

/**
* uninstall hook
*/
register_uninstall_hook( __FILE__ , 'cpvars_cleanup' );
function cpvars_cleanup (){
	if ( 1 == get_option( 'cpvars-cleanup' ) ){
		delete_option( 'cpvars-cleanup' );
		delete_option( 'cpvars-doeverywhere' );
		delete_option( 'cpvars-vars' );
		delete_option( 'cpvars-whocanedit' );
	}
}

register_activation_hook( __FILE__, 'cpvars_activate' );
function cpvars_activate() {
	// remove old option
    delete_option( 'cpvars-doeval' );
    if ( !get_option( 'cpvars-whocanedit' ) || true){
    	update_option ( 'cpvars-whocanedit', 'manage_options');
    };
}

?>