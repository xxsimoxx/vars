<?php
/*
* Plugin Name: CPvars
* Plugin URI: https://www.gieffeedizioni.it/
* Description: Vars in shortcodes 
* Version: 0.2
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it
*/

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if (!defined('ABSPATH')) die('-1');

// Admin section
add_action('admin_menu', 'cpvars_create_menu');
function cpvars_create_menu() {
	add_menu_page('CPvars settings', 'CPvars settings', 'administrator', __FILE__, 'cpvars_settings_page' ,'dashicons-editor-textcolor' );
}

function cpvars_settings_page() {

if ( !current_user_can('manage_options') ) {
   exit;
}

if ( isset( $_POST["allvars"] ) || isset( $_POST["doeverywhere"] ) || isset( $_POST["cleanup"] ) ){
	check_admin_referer( 'cpvars-admin' );
	parse_str( $_POST["allvars"], $testvars );
	update_option( 'cpvars-vars', $_POST["allvars"] );
	if ( isset( $_POST["doeverywhere"] ) ){
		update_option( 'cpvars-doeverywhere', 1 );
	} else {
		update_option( 'cpvars-doeverywhere', 0 );
	};
	if ( isset( $_POST["cleanup"] ) ){
		update_option( 'cpvars-cleanup', 1 );
	} else {
		update_option( 'cpvars-cleanup', 0 );
	};
} else {
	$coded_options = get_option( 'cpvars-vars' );
	parse_str( $coded_options, $testvars );
};


add_action( 'admin_footer', 'cpvars_scripts' );

function cpvars_scripts() { ?>
	<script type="text/javascript" >
		jQuery(".cpvars-key, .cpvars-value, .doeverywhere, .cleanup").change(function() {
		    jQuery("#cpvars-submit").prop("disabled", false);
		    jQuery("#cpvars-submit").val('Save');
		});
		
		jQuery("#cpvars-form").submit( function(eventObj) {
			var vars = new Object();
			jQuery(".cpvars-keyvalue").each(function(){
				var key = jQuery(this).find('.cpvars-key').val();
				var value = jQuery(this).find('.cpvars-value').val();
				vars[key] = value;
			});
			jQuery(this).append('<input type="hidden" name="allvars" value=" ' + jQuery.param(vars) + '">');
		});
		
		jQuery('.cpvars-delete').click(function(){
			jQuery(this).closest("tr").remove();
		    jQuery("#cpvars-submit").prop("disabled", false);
		    jQuery("#cpvars-submit").val('Save');
		});
		
		jQuery('.cpvars-add').click(function(){
			jQuery(".form-table").append('<tr valign="top" class="cpvars-keyvalue"><td><input type="text" size="20" class="cpvars-key" value="name" /></td><td><input type="text" size="100" class="cpvars-value" value="content" /></td><td><span class="dashicons dashicons-trash cpvars-delete"></span></td></tr>');
			jQuery("#cpvars-submit").prop("disabled", false);
			jQuery("#cpvars-submit").val('Save');
		});

	</script> <?php
}
?>

<div class="wrap">

<form method="POST" id="cpvars-form"  >
<input type="checkbox" name="doeverywhere" class="doeverywhere" <?php if ( 1 == get_option( 'cpvars-doeverywhere' ) ){echo "checked='checked'";}; ?> >Do shortcodes anywhere.</input>
<input type="checkbox" name="cleanup" class="cleanup" <?php if ( 1 == get_option( 'cpvars-cleanup' ) ){echo "checked='checked'";}; ?> >Delete plugin data at uninstall.</input>
<style>
.form-table {
  width: auto !important;
}
</style>
    <table class="form-table">
    
<?php
	foreach ( $testvars as $key => $value ){
		echo '<tr valign="top" class="cpvars-keyvalue"><td ><input type="text" size="20" class="cpvars-key" value="' . $key . '" /></td>';
		echo '<td ><input type="text" size="100" class="cpvars-value" value="' . htmlspecialchars( $value ) . '" /></td><td><span class="dashicons dashicons-trash cpvars-delete"></span></td></tr>'; 
	}
?>
    </table>
    <span class="dashicons dashicons-plus-alt cpvars-add"></span>
    <?php wp_nonce_field( 'cpvars-admin' ); ?>
    <input type="submit" value="Saved" id="cpvars-submit" class="button button-primary button-large" disabled>
</form>


</div>

<?php } 

// shortcode section
add_shortcode('cpv', 'cpv');
function cpv( $atts, $content = null ) {
	$coded_options = get_option( 'cpvars-vars' );
	parse_str( $coded_options, $testvars );
	if ( isset( $testvars[$content] ) ){
		return $testvars[$content];
	} elseif ( current_user_can('manage_options') ) {
		$url = admin_url( 'admin.php?page=cpvars%2Fcpvars.php' );
		return "$content is not defined. Define it <a href='$url'>here</a>. (only admin see this)";
	} else {
		return "";
	}
}

// do shortcodes everywhere section
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

// uninstall section
register_uninstall_hook( __FILE__ , 'cpvars_cleanup' );
function cpvars_cleanup (){
	if ( 1 == get_option( 'cpvars-cleanup' ) ){
		delete_option( 'cpvars-cleanup' );
		delete_option( 'cpvars-doeverywhere' );
		delete_option( 'cpvars-vars' );
	}
}


?>