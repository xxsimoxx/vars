<?php
/**
 * Plugin Name: vars
 * Plugin URI: https://software.gieffeedizioni.it
 * Description: Vars in shortcodes
 * Version: 2.1.2
 * Requires CP: 1.1
 * Requires PHP: 5.6
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it
 * Text Domain: vars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
};

define( 'VARS_VERSION', '2.1.2' );

vars_handle_update();

/**
 * Introduce vars_handle_update to handle format changes:
 * 2.1.0 vars-vars option is now an array.
 *
 * @since 2.1.0
 * @return null
 */
function vars_handle_update() {
	$current = get_option( 'vars-version', false );
	// In the future here compare version.
	if ( false !== $current ) {
		return;
	}
	$oldvars = get_option( 'vars-vars', '' );
	if ( is_array( $oldvars ) ) {
		// Seems already updated.
		return;
	}
	// Migrate to new format of vars-vars option introduced in 2.1.0.
	parse_str( $oldvars, $newvars );
	update_option( 'vars-vars', $newvars );
	update_option( 'vars-version', VARS_VERSION );
}

// Load text domain.
add_action( 'plugins_loaded', 'vars_load_textdomain' );
function vars_load_textdomain() {
	load_plugin_textdomain( 'vars', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Add auto updater.
require_once 'classes/UpdateClient.class.php';

/*
 *
 * Add a settings link in plugins page.
 *
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'vars_pal' );
function vars_pal( $links ) {
	if ( current_user_can( get_option( 'vars-whocanedit', 'manage_options' ) ) ) {
		$link = '<a href="' . admin_url( 'tools.php?page=vars-options' ) . '" title="' . __( 'Settings', 'vars' ) . '"><i class="dashicon dashicons-admin-generic" style="font: 16px dashicons;vertical-align: text-bottom;"></i></a>';
		array_unshift( $links, $link );
	}
	return $links;
}

/**
 *
 * Functions to handle and render options
 */
function vars_save_security_settings( $admin_referer ) {
	$error_string = '';
	if ( isset( $_POST['doeverywhere'] ) || isset( $_POST['cleanup'] ) || isset( $_POST['whocanedit'] ) ) {
		check_admin_referer( $admin_referer );
		if ( isset( $_POST['doeverywhere'] ) ) {
			update_option( 'vars-doeverywhere', '1' );
		} else {
			update_option( 'vars-doeverywhere', '0' );
		}
		if ( isset( $_POST['cleanup'] ) ) {
			update_option( 'vars-cleanup', '1' );
		} else {
			update_option( 'vars-cleanup', '0' );
		}
		if ( isset( $_POST['whocanedit'] ) ) {
			// Hard sanitize $_POST element.
			$whocanedit = preg_replace( '/[^a-z_]/', '', $_POST['whocanedit'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( current_user_can( $whocanedit ) ) {
				update_option( 'vars-whocanedit', $whocanedit );
			} else {
				/* translators: %s is the capability. Only use the bold tag <b>. */
				$error_string = '<span style="color:red;">' . wp_kses( sprintf( __( 'You don\'t have <b>%s</b> capability.', 'vars' ), $whocanedit ), array( 'b' => array() ) ) . '</span>';
			}
		}
	}
	return $error_string;
};

function vars_render_security_settings( $errors ) {
	?>
	<input type="checkbox" name="doeverywhere" value="1" class="doeverywhere" <?php checked( get_option( 'vars-doeverywhere', '0' ), '1' ); ?>>
	<?php esc_html_e( 'Do shortcodes anywhere.', 'vars' ); ?> </input><br>
	<input type="checkbox" name="cleanup" value="1" class="cleanup" <?php checked( get_option( 'vars-cleanup', '0' ), '1' ); ?>>
	<?php esc_html_e( 'Delete plugin data at uninstall.', 'vars' ); ?></input><br>
	<?php esc_html_e( 'User capability requested to edit vars:', 'vars' ); ?>
	<input type="text" name="whocanedit" class="whocanedit" value="<?php echo esc_html( get_option( 'vars-whocanedit', 'manage_options' ) ); ?>">
	<?php
	// As long as $errors is passed from vars_save_security_settings is sanitized there.
	echo $errors; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$users    = get_users( array( 'fields' => array( 'ID' ) ) );
	$count    = 0;
	$userlist = '';
	foreach ( $users as $user_id ) {
		$meta = get_user_meta( $user_id->ID );
		if ( user_can( $user_id->ID, get_option( 'vars-whocanedit', 'manage_options' ) ) ) {
			$count++;
			$userlist .= $meta['nickname'][0] . ', ';
		}
	}
	echo '<p>';
	/* translators: 1 is the number of user. 2 is the list of users. You can use only the <i> tag in translation */
	$usermessage = sprintf( _n( '%1$d user has <i>%3$s</i> capability and so can change vars: %2$s.', '%1$d users have <i>%3$s</i> capability and so can change vars: %2$s.', $count, 'vars' ), $count, rtrim( $userlist, ', ' ), get_option( 'vars-whocanedit', 'manage_options' ) );
	echo wp_kses( $usermessage, array( 'i' => array() ) );
	echo '</p><hr>';
};

/*
 *
 * Admin section: add a sumbenu to the tools menu
 *
 */
add_action( 'admin_footer', 'vars_admin_script' );
function vars_admin_script() {
	$screen = get_current_screen();
	if ( 'tools_page_vars-options' === $screen->id ) {
		wp_enqueue_script( 'vars_admin', plugins_url( 'js/vars-admin.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_localize_script(
			'vars_admin',
			'objectL10n',
			array(
				'save'    => __( 'Save', 'vars' ),
				'name'    => __( 'name', 'vars' ),
				'content' => __( 'content', 'vars' ),
			)
		);
	}
}

add_action( 'admin_menu', 'vars_create_menu' );
function vars_create_menu() {
	if ( current_user_can( get_option( 'vars-whocanedit', 'manage_options' ) ) ) {
		$page = add_submenu_page(
			'tools.php',
			__( 'SETTINGS_PAGE_TITLE', 'vars' ),
			__( 'SETTINGS_PAGE_NAME', 'vars' ),
			get_option( 'vars-whocanedit' ),
			'vars-options',
			'vars_settings_page'
		);
	}
}

function vars_settings_page() {
	$cap_error = '';
	if ( ! current_user_can( get_option( 'vars-whocanedit', 'manage_options' ) ) ) {
		exit;
	}
	if ( isset( $_POST['allvars'] ) || isset( $_POST['doeverywhere'] ) || isset( $_POST['cleanup'] ) || isset( $_POST['whocanedit'] ) ) {
		check_admin_referer( 'vars-admin' );
		// Var can contain almost anything so here we trust our user (default capability to edit this is manage options).
		// This is sanitized in the next lines.
		parse_str( wp_unslash( $_POST['allvars'] ), $testvars ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$sanitized_vars = array();
		foreach ( $testvars as $key => $value ) {
			$sanitized_vars[ preg_replace( '/[^A-Za-z_0-9]/', '_', $key ) ] = $value;
		}
		update_option( 'vars-vars', $sanitized_vars );
		if ( current_user_can( 'manage_options' ) ) {
			$cap_error = vars_save_security_settings( 'vars-admin' );
		}
	} else {
		$sanitized_vars = get_option( 'vars-vars', array() );
	}
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
			content:url("<?php echo esc_url( plugins_url( 'icon.svg', __FILE__ ) ); ?>");
			padding: 0 5px 0 0;
		}
	</style>
	<div class="wrap">
	<?php
	// Text about plugin usage I prefer storing in the translations.
	/* translators: only use <h2> <h2> <p> <pre> tags.*/
	$header        = __( 'HEADERTEXT', 'vars' );
	$allowedmarkup = array(
		'h2'  => array(),
		'h3'  => array(),
		'p'   => array(),
		'pre' => array(),
	);
	echo wp_kses( $header, $allowedmarkup );
	?>
	<hr>
	<form method="POST" id="vars-form"  >
	<?php
	if ( current_user_can( 'manage_options' ) && ! function_exists( '\add_security_page' ) ) {
		vars_render_security_settings( $cap_error );
	}
	if ( current_user_can( 'manage_options' ) && function_exists( '\add_security_page' ) ) {
		echo '<a href="' . esc_url( admin_url( 'security.php?page=vars' ) ) . '" title="' . esc_html__( 'Security settings', 'vars' ) . '"><i class="dashicons-before dashicons-shield">' . esc_html__( 'Edit security settings', 'vars' ) . '</i></a><hr>';
	}
	?>
		<table class="form-table">
	<?php
	foreach ( $sanitized_vars as $key => $value ) {
		echo '<tr valign="top" class="vars-keyvalue"><td ><input type="text" size="20" class="vars-key" value="' . esc_html( $key ) . '" /></td>';
		echo '<td ><input type="text" size="100" class="vars-value" value="' . esc_html( $value ) . '" /></td><td><a class="dashicons dashicons-trash vars-delete"></a></td></tr>';
	}
	?>
		</table>
	<button type="button" class="button button-large button-primary vars-add"><?php esc_html_e( 'Add', 'vars' ); ?></button>
		<?php wp_nonce_field( 'vars-admin' ); ?>
		<input type="submit" value="<?php esc_html_e( 'Saved', 'vars' ); ?>" id="vars-submit" class="button button-primary button-large" disabled>
	</form>
	</div>
	<?php
}

/**
 *
 * Admin section: add security page
 */
add_action( 'admin_menu', 'vars_create_security_menu' );
function vars_create_security_menu() {
	if ( function_exists( '\add_security_page' ) ) {
		add_security_page(
			__( 'SECURITY_SETTINGS_PAGE_TITLE', 'vars' ),
			__( 'SECURITY_SETTINGS_PAGE_NAME', 'vars' ),
			dirname( plugin_basename( __FILE__ ) ),
			'vars_security_page'
		);
	};
};

function vars_security_page() {
	$cap_error = '';
	if ( isset( $_POST['doeverywhere'] ) || isset( $_POST['cleanup'] ) || isset( $_POST['whocanedit'] ) ) {
		check_admin_referer( 'vars-security' );
		$cap_error = vars_save_security_settings( 'vars-security' );
	}
	?>
	<div class="wrap">
	<h2>vars</h2>
	<style>
		h2::before {
			content:url("<?php echo esc_url( plugins_url( 'icon.svg', __FILE__ ) ); ?>");
			padding: 0 5px 0 0;
		}
	</style>
	<h3><?php esc_html_e( 'Security settings.', 'vars' ); ?></h3>
	<hr>
	<form method="POST" id="vars-security"  >
	<?php
	vars_render_security_settings( $cap_error );
	wp_nonce_field( 'vars-security' );
	?>
		<input type="submit" class="button button-primary button-large" value="<?php esc_html_e( 'Save', 'vars' ); ?>" >
	</form>
	</div>
	<?php
}

/**
 *
 * Shortcode section
 */
add_shortcode( 'vars', 'cpv' );
function cpv( $atts, $content = null ) {
	$testvars = get_option( 'vars-vars', array() );
	if ( isset( $testvars[ $content ] ) ) {
		$prefilter_retval = $testvars[ $content ];
		$filtered_retval  = apply_filters( 'vars_output', $prefilter_retval );
		return $filtered_retval;
	} elseif ( current_user_can( get_option( 'vars-whocanedit', 'manage_options' ) ) ) {
		$url = admin_url( 'tools.php?page=vars-options' );
		/* translators: 1 is the var not defined. 2 is the url of the admin page */
		return sprintf( __( '%1$s is not defined. Define it <a href="%2$s">here</a>. (only you can see this message)', 'vars' ), $content, $url );
	} else {
		return '';
	}
}

function vars_do( $var ) {
	return cpv( '', $var );
}

/**
 *
 * Do shortcodes everywhere section
 */
if ( '1' === get_option( 'vars-doeverywhere', '0' ) ) {
	$tags = array(
		'single_post_title',
		'the_title',
		'widget_text',
		'widget_title',
		'bloginfo',
		'get_post_metadata',
	);
	foreach ( $tags as $single_tag ) {
		add_filter( $single_tag, 'do_shortcode', 10 );
	}
}

/**
 *
 * Add a menu to mce
 */
foreach ( array( 'post.php', 'post-new.php' ) as $hook ) {
	add_action( "admin_head-$hook", 'vars_admin_head' );
}

function vars_admin_head() {
	$testvars = get_option( 'vars-vars', array() );
	if ( array() === $testvars ) {
		return;
	}
	$vars_dynamic_mce  = '';
	$vars_dynamic_mce5 = '';
	foreach ( $testvars as $var => $value ) {
		if ( strlen( $value ) <= 10 ) {
			$example_data = wp_strip_all_tags( $value );
		} else {
			$example_data = substr( wp_strip_all_tags( $value ), 0, 12 ) . '...';
		}
		$example_data       = addslashes( $example_data );
		$example_data       = ' (' . $example_data . ')';
		$vars_dynamic_mce  .=
			'{text: "' . $var . $example_data . '",onclick: function() {tinymce.activeEditor.insertContent("[vars]' . $var . '[/vars]"); }},';
		$vars_dynamic_mce5 .=
			'{type:"menuitem", text: "' . $var . $example_data . '",onAction: function() {tinymce.activeEditor.insertContent("[vars]' . $var . '[/vars]"); }},';

	}
	$vars_dynamic_mce  = '$vars_dynmenu=[' . $vars_dynamic_mce . ']';
	$vars_dynamic_mce5 = '$vars_dynmenu=[' . $vars_dynamic_mce5 . ']';
	echo '<script type="text/javascript">';
	// Var can contain almost anything so here we trust our user (default capability to edit this is manage options).
	echo vars_is_mce_5() ? $vars_dynamic_mce5 : $vars_dynamic_mce; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</script>';
}

add_action( 'admin_head', 'vars_add_mce_menu' );
function vars_add_mce_menu() {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$testvars = get_option( 'vars-vars', array() );
	if ( array() === $testvars ) {
		return;
	}
	if ( 'true' === get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'vars_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'vars_register_mce_menu' );
	}
}

function vars_register_mce_menu( $buttons ) {
	array_push( $buttons, 'vars_mce_menu' );
	return $buttons;
}

function vars_is_mce_5() {
	global $tinymce_version;
	return isset( $tinymce_version ) && substr( $tinymce_version, 0, 1 ) === '5';
}

function vars_add_tinymce_plugin( $plugin_array ) {
	$js                            = vars_is_mce_5() ? 'js/vars-mce-menu-5.js' : 'js/vars-mce-menu.js';
	$plugin_array['vars_mce_menu'] = plugins_url( $js, __FILE__ );
	return $plugin_array;
}

/**
 *
 * Activation and uninstall hooks
 */
register_uninstall_hook( __FILE__, 'vars_cleanup' );
function vars_cleanup() {
	if ( '1' === get_option( 'vars-cleanup', '0' ) ) {
		delete_option( 'vars-cleanup' );
		delete_option( 'vars-doeverywhere' );
		delete_option( 'vars-vars' );
		delete_option( 'vars-whocanedit' );
		delete_option( 'vars-version' );
	}
}

register_activation_hook( __FILE__, 'vars_activate' );
function vars_activate() {
	// If permission options not set, let only admin make changes.
	if ( false === get_option( 'vars-whocanedit' ) ) {
		update_option( 'vars-whocanedit', 'manage_options' );
	}
}
