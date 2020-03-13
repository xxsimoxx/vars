<?php
/**
 * Plugin Name: vars
 * Plugin URI: https://software.gieffeedizioni.it
 * Description: Vars in shortcodes
 * Version: 2.0.3
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it
 * Text Domain: vars
 */

if (!defined('ABSPATH')){
	die('-1');
};

// Load text domain
add_action( 'plugins_loaded', 'vars_load_textdomain' );
function vars_load_textdomain() {
	load_plugin_textdomain( 'vars', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Add auto updater
// https://codepotent.com/classicpress/plugins/update-manager/
require_once( 'classes/UpdateClient.class.php' );

/*
 *
 * Add a settings link in plugins page
 *
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'vars_pal' );
function vars_pal( $links ) {
	if ( current_user_can( get_option( 'vars-whocanedit' ) ) ) {
		$link = '<a href="' . admin_url( 'tools.php?page=vars-options' ) . '" title="' . __( 'Settings', 'vars' ) . '"><i class="dashicon dashicons-admin-generic"></i></a>';
		array_unshift( $links, $link );
	}
	return $links;
}

/*
 *
 * ClassicPress PR #484 added this CSS in v. 1.1.0
 * This is for backward compatibility.
 *
 */
add_action('admin_enqueue_scripts', 'vars_admin_style');
function vars_admin_style( $hook ) {
	if ( ! function_exists( 'classicpress_version' ) || version_compare( '1.1.0', classicpress_version() , '>' )  ){
		if ( 'plugins.php' == $hook ) {
				wp_enqueue_style( 'vars_compatibility_css', plugins_url( 'css/vars-compatibility.css', __FILE__ ) );
		}
	}
}

/*
 *
 * Functions to handle and render options
 *
 */
function vars_save_security_settings( $admin_referer ) {
	$error_string = "";
	if ( isset( $_POST["doeverywhere"] ) || isset( $_POST["cleanup"] ) || isset( $_POST["whocanedit"] )){
		check_admin_referer( $admin_referer );
		if ( isset( $_POST["doeverywhere"] )  ){
			update_option( 'vars-doeverywhere', 1 );
		} else {
			update_option( 'vars-doeverywhere', 0 );
		};
		if ( isset( $_POST["cleanup"] )  ){
			update_option( 'vars-cleanup', 1 );
		} else {
			update_option( 'vars-cleanup', 0 );
		};
		if ( isset( $_POST["whocanedit"] )  ){
			if ( current_user_can( $_POST["whocanedit"] ) ) {
				update_option( 'vars-whocanedit', preg_replace( '/[^a-z_]/', '', $_POST["whocanedit"] ) );
			} else {
				/*Translators: %s is the capability */
				$error_string = '<span style="color:red;">' . sprintf( __( 'You don\'t have <b>%s</b> capability.', 'vars' ), $_POST["whocanedit"] ) . '</span>';
			};
		};
	};
	return $error_string;
};

function vars_render_security_settings( $errors ) {
?>
	<input type="checkbox" name="doeverywhere" class="doeverywhere" <?php if ( 1 == get_option( 'vars-doeverywhere' ) ) echo "checked='checked'"; ?>>
	<?php _e( 'Do shortcodes anywhere.', 'vars' ); ?> </input><br>
	<input type="checkbox" name="cleanup" class="cleanup" <?php if ( 1 == get_option( 'vars-cleanup' ) ) echo "checked='checked'"; ?> >
	<?php _e( 'Delete plugin data at uninstall.', 'vars' ); ?></input><br>
	<?php _e( 'User capability requested to edit vars:', 'vars' ); ?>
	<input type="text" name="whocanedit" class="whocanedit" value="<?php echo get_option( 'vars-whocanedit' ); ?>">
	<?php
	echo $errors;
	// let's see who can change the vars
	$users = get_users( array( 'fields' => array( 'ID' ) ) );
	$count = 0;
	$userlist = "";
	foreach($users as $user_id) {
		$meta = get_user_meta ( $user_id->ID );
		if ( user_can( $user_id->ID, get_option( 'vars-whocanedit' ) ) ) {
			$count++;
			$userlist .= $meta['nickname'][0] . ", ";
		};
	}
	echo "<p>";
	/* translators: 1 is the number of user. 2 is the list of users */
	printf(_n('%1$d user has <i>%3$s</i> capability and so can change vars: %2$s.', '%1$d users have <i>%3$s</i> capability and so can change vars: %2$s.', $count, 'vars'), $count, rtrim( $userlist, ', ' ), get_option( 'vars-whocanedit' ) );
	echo "</p><hr>";
};

/*
 *
 * Admin section: add a sumbenu to the tools menu
 *
 */
add_action( 'admin_footer', 'vars_admin_script' );
function vars_admin_script( ) {
	$screen = get_current_screen();
	if ( 'tools_page_vars-options' == $screen->id ){
		wp_enqueue_script( 'vars_admin', plugins_url( 'js/vars-admin.js', __FILE__ ), array('jquery'), '1.0' );
		wp_localize_script( 'vars_admin', 'objectL10n',
			array(
				'save'     => __( 'Save', 'vars' ),
				'name'     => __( 'name', 'vars' ),
				'content'  => __( 'content', 'vars' ),
			)
		);
	}
}

add_action( 'admin_menu', 'vars_create_menu' );
function vars_create_menu() {
	if ( current_user_can( get_option( 'vars-whocanedit' ) ) ) {
		$page=add_submenu_page(
			'tools.php',
			__('SETTINGS_PAGE_TITLE', 'vars'),
			__('SETTINGS_PAGE_NAME', 'vars'),
			get_option( 'vars-whocanedit' ),
			'vars-options',
			'vars_settings_page'
		);
	}
}

function vars_settings_page() {
	if ( ! current_user_can( get_option( 'vars-whocanedit' ) ) ) {
		exit;
	}
	if ( isset( $_POST["allvars"] ) || isset( $_POST["doeverywhere"] ) || isset( $_POST["cleanup"] ) || isset( $_POST["whocanedit"] )){
		check_admin_referer( 'vars-admin' );
		parse_str( $_POST["allvars"], $testvars );
		update_option( 'vars-vars', $_POST["allvars"] );
		if ( current_user_can( 'manage_options' ) ) {
			$cap_error = vars_save_security_settings( 'vars-admin' );
		};
	} else {
		$coded_options = get_option( 'vars-vars' );
		parse_str( $coded_options, $testvars );
	};
	// text about plugin usage I prefer storing in the translations
	$header = __("HEADERTEXT" , 'vars' );
	?>
	<style>
		.form-table {
			width: auto !important;
			padding:100px;
		}
		.code {
			font-family: "Courier New", Courier, mono;
		}
		h2::before {
			content:url("<?php echo plugins_url( 'icon.svg', __FILE__ ); ?>");
			padding: 0 5px 0 0;
		}
	</style>
	<div class="wrap">
	<?php echo $header; ?>
	<hr>
	<form method="POST" id="vars-form"  >
	<?php
	if ( current_user_can('manage_options') && ! function_exists( '\add_security_page' ) ){
		vars_render_security_settings( $cap_error );
	};
	if ( current_user_can('manage_options') && function_exists( '\add_security_page' ) ) {
		$security_link = '<a href="' . admin_url( 'security.php?page=vars' ) . '" title="' . __( 'Security settings', 'vars' ) . '"><i class="dashicons-before dashicons-shield">';
		echo $security_link . __( "Edit security settings",'vars') . '</i></a><hr>';
	};
	?>
		<table class="form-table">
	<?php
	foreach ( $testvars as $key => $value ){
		echo '<tr valign="top" class="vars-keyvalue"><td ><input type="text" size="20" class="vars-key" value="' . $key . '" /></td>';
		echo '<td ><input type="text" size="100" class="vars-value" value="' . htmlspecialchars( $value ) . '" /></td><td><a class="dashicons dashicons-trash vars-delete"></a></td></tr>';
	}
	?>
		</table>
	<button type="button" class="button button-large button-primary vars-add"><?php _e( 'Add', 'vars' ); ?></button>
		<?php wp_nonce_field( 'vars-admin' ); ?>
		<input type="submit" value="<?php _e( 'Saved', 'vars' ); ?>" id="vars-submit" class="button button-primary button-large" disabled>
	</form>
	</div>
	<?php
}

/**
 *
 * Admin section: add security page
 *
 */
add_action( 'admin_menu', 'vars_create_security_menu' );
function vars_create_security_menu() {
	if ( function_exists( '\add_security_page' ) ) {
		add_security_page(
			__('SECURITY_SETTINGS_PAGE_TITLE', 'vars'),
			__('SECURITY_SETTINGS_PAGE_NAME', 'vars'),
			dirname( plugin_basename( __FILE__ ) ),
			'vars_security_page'
		);
	};
};

function vars_security_page() {
	$cap_error = "";
	if ( isset( $_POST["doeverywhere"] ) || isset( $_POST["cleanup"] ) || isset( $_POST["whocanedit"] )){
		check_admin_referer( 'vars-security' );
		$cap_error = vars_save_security_settings( 'vars-security' );
	};
	?>
	<div class="wrap">
	<h2>vars</h2>
	
	<style>
		h2::before {
			content:url("<?php echo plugins_url( 'icon.svg', __FILE__ ); ?>");
			padding: 0 5px 0 0;
		}
	</style>
	<h3><?php _e( 'Security settings.', 'vars' ); ?></h3>
	<hr>
	<form method="POST" id="vars-security"  >
	<?php
	vars_render_security_settings( $cap_error );
	wp_nonce_field( 'vars-security' );
	?>
		<input type="submit" value="<?php _e( 'Save', 'vars' ); ?>" >
	</form>
	</div>
	<?php
}

/**
 *
 * shortcode section
 *
 */
add_shortcode('vars', 'cpv');
function cpv( $atts, $content = null ) {
	$coded_options = get_option( 'vars-vars' );
	parse_str( $coded_options, $testvars );
	if ( isset( $testvars[$content] ) ){
		$prefilter_retval = $testvars[$content];
		$filtered_retval = apply_filters( 'vars_output', $prefilter_retval );
		return $filtered_retval;
	} elseif ( current_user_can( get_option( 'vars-whocanedit' ) ) ) {
		$url = admin_url( 'tools.php?page=vars-options' );
		/* translators: 1 is the var not defined. 2 is the url of the admin page */
		return sprintf ( __('%1$s is not defined. Define it <a href="%2$s">here</a>. (only you can see this message)', 'vars'), $content, $url );
	} else {
		return "";
	}
}

function vars_do ( $var ) {
	return cpv( '', $var );
};

/**
 *
 * do shortcodes everywhere section
 *
 */
if ( 1 == get_option( 'vars-doeverywhere' ) ){
	$vars_shortcodeseverywhere_pryority = 10;
	$tags = [
		'single_post_title',
		'the_title',
		'widget_text',
		'widget_title',
		'bloginfo',
		'get_post_metadata'
	];
	foreach ( $tags as $tag ){
		add_filter( $tag, 'do_shortcode', $vars_shortcodeseverywhere_pryority );
	}
}

/**
 *
 * Add a menu to mce
 *
 */
foreach ( array('post.php','post-new.php') as $hook ) {
	add_action( "admin_head-$hook", 'vars_admin_head' );
}

function vars_admin_head() {
	$coded_options = get_option( 'vars-vars' );
	parse_str( $coded_options, $testvars );
	$vars_dynamic_mce = "";
	foreach ( $testvars as $var => $value){
		if ( strlen( $value ) <= 10 ){
			$example_data = wp_strip_all_tags( $value );
		} else {
			$example_data = substr( wp_strip_all_tags( $value ), 0, 12) . "...";
		};
		$example_data = ' (' . $example_data . ')';
		$vars_dynamic_mce .=
			'{text: "' . $var . $example_data . '",onclick: function() {tinymce.activeEditor.insertContent("[vars]' . $var . '[/vars]"); }},';
	};
	$vars_dynamic_mce = '$vars_dynmenu=[' . $vars_dynamic_mce . ']';
	?>
	<script type='text/javascript'>
		<?php echo $vars_dynamic_mce; ?>
	</script>
	<?php
}

add_action('admin_head', 'vars_add_mce_menu');
function vars_add_mce_menu() {
	if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'vars_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'vars_register_mce_menu' );
	}
}

function vars_register_mce_menu( $buttons ) {
	array_push( $buttons, 'vars_mce_menu' );
	return $buttons;
}

function vars_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['vars_mce_menu'] = plugins_url( 'js/vars-mce-menu.js', __FILE__ );
	return $plugin_array;
}

/**
 *
 * activation and uninstall hooks
 *
 */
register_uninstall_hook( __FILE__, 'vars_cleanup' );
function vars_cleanup () {
	if ( 1 == get_option( 'vars-cleanup' ) ){
		delete_option( 'vars-cleanup' );
		delete_option( 'vars-doeverywhere' );
		delete_option( 'vars-vars' );
		delete_option( 'vars-whocanedit' );
	}
}

register_activation_hook( __FILE__, 'vars_activate' );
function vars_activate() {
	// If permission options not set, let admin make changes
	if ( false === get_option( 'vars-whocanedit' ) ){
		update_option ( 'vars-whocanedit', 'manage_options');
	};
}

?>