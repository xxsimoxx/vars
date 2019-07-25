<?php
/*
* Plugin Name: CPvars
* Plugin URI: https://www.gieffeedizioni.it/ClassicPress
* Description: Vars in shortcodes 
* Version: 0.2
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Author: Gieffe edizioni srl
* Author URI: https://www.gieffeedizioni.it/ClassicPress
* Text Domain: cpvars
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('ABSPATH')) die('-1');

// Load text domain
load_plugin_textdomain( 'cpvars', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Admin section
add_action( 'admin_footer', 'cpvars_admin_script' );
function cpvars_admin_script( ) {
	$screen = get_current_screen(); 
	if ( 'tools_page_cpvars' == $screen->id ){
		wp_enqueue_script( 'cpvars_admin', plugins_url( 'js/cpvars-admin.js', __FILE__ ), array(), '1.0' );
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
	$page=add_submenu_page('tools.php', 'CPvars', 'CPvars', 'manage_options','cpvars', 'cpvars_settings_page' ,'dashicons-editor-textcolor' );
}

function cpvars_settings_page() {

if ( !current_user_can('manage_options') ) {
   exit;
}

// Directly manage options
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

$header = __("HEADERTEXT" , 'cpvars' );
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
<input type="checkbox" name="doeverywhere" class="doeverywhere" <?php if ( 1 == get_option( 'cpvars-doeverywhere' ) ){echo "checked='checked'";};?>> 
<?php _e( 'Do shortcodes anywhere.', 'cpvars' )?> </input><br>
<input type="checkbox" name="cleanup" class="cleanup" <?php if ( 1 == get_option( 'cpvars-cleanup' ) ){echo "checked='checked'";}; ?> >
<?php _e( 'Delete plugin data at uninstall.', 'cpvars' )?></input>
<hr>



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

<?php } 
/**
* shortcode section
*/
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

function cpvars_add_mce_menu() {
            if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
                       return;
               }
           if ( 'true' == get_user_option( 'rich_editing' ) ) {
               add_filter( 'mce_external_plugins', 'cpvars_add_tinymce_plugin' );
               add_filter( 'mce_buttons', 'cpvars_register_mce_menu' );
               }
}
add_action('admin_head', 'cpvars_add_mce_menu');


function cpvars_register_mce_menu( $buttons ) {
            array_push( $buttons, 'cpvars_mce_menu' );
            return $buttons;
}

function cpvars_add_tinymce_plugin( $plugin_array ) {
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
	}
}


?>