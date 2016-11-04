<?php
/*
	Plugin Name: Uploads Path and URI Enabler
	Description: WordPress 3.5 removes the setting fields to change the media upload path and url. This plugin enable them again. Note that as long as your fields are not empty, you can disable this plugin.
	Plugin URI: https://github.com/joethomas/uploads-path-uri-enabler
	Version: 1.0.4
	Author: Joe Thomas
	Author URI: http://joethomas.co
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	Text Domain: uploads-path-uri-enabler
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

// Check WordPress Version.
global $wp_version;

if ( ! is_admin() || is_multisite() || version_compare( $wp_version, '3.5' ) < 0 ) {
	return;
}


/* Setup Plugin
==============================================================================*/

/**
 * Define the constants for use within the plugin
 */

// Plugin
function joeuploadspurie_get_plugin_data() {
	$plugin = get_plugin_data( __FILE__, false, false );

	define( 'JOEUPLOADSPURIE_VER', $plugin['Version'] );
	define( 'JOEUPLOADSPURIE_PREFIX', $plugin['TextDomain'] );
	define( 'JOEUPLOADSPURIE_NAME', $plugin['Name'] );
}
add_action( 'init', 'joeuploadspurie_get_plugin_data' );


/* Init
==============================================================================*/

function joeuploadspurie_init() {
	if ( ! get_option( 'upload_url_path' ) && ! ( get_option( 'upload_path' ) !== 'wp-content/uploads' && get_option( 'upload_path' ) ) ) {
		register_setting( 'media', 'upload_path', 'esc_attr' );
		register_setting( 'media', 'upload_url_path', 'esc_url' );
		add_settings_field( 'upload_path', __( 'Store uploads in this folder' ), 'joeuploadspurie_uploads_path', 'media', 'uploads', array( 'label_for' => 'upload_path' ) );
		add_settings_field( 'upload_url_path', __( 'Full URL path to files' ), 'joeuploadspurie_uploads_url_path', 'media', 'uploads', array( 'label_for' => 'upload_url_path' ) );
	}
}
add_action( 'load-options-media.php', 'joeuploadspurie_init' );
add_action( 'load-options.php', 'joeuploadspurie_init' );

function joeuploadspurie_uploads_path( $args ) {
	global $wp_version;
	?>
	<input name="upload_path" type="text" id="upload_path" value="<?php echo esc_attr( get_option( 'upload_path' ) ); ?>" class="regular-text code" />
	<p class="description"><?php
		if ( version_compare( $wp_version, '4.4' ) < 0 ) {
			_e( 'Default is <code>wp-content/uploads</code>' );
		} else {
			/* translators: %s: wp-content/uploads */
			printf( __( 'Default is %s' ), '<code>wp-content/uploads</code>' );
		}
	?></p>
	<?php
}


function joeuploadspurie_uploads_url_path( $args ) {
	?>
	<input name="upload_url_path" type="text" id="upload_url_path" value="<?php echo esc_attr( get_option( 'upload_url_path' ) ); ?>" class="regular-text code" />
	<p class="description"><?php _e( 'Configuring this is optional. By default, it should be blank.' ); ?></p>
	<?php
}

/* Plugin Updates
==============================================================================*/

/**
 * Do not update plugin from WordPress repository
 *
 * @since 1.0.0
 */
function joeuploadspurie_do_not_update_plugin_wp( $r, $url ) {

	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {

		return $r; // Not a plugin update request. Bail immediately.

	}

	$plugins = unserialize( $r['body']['plugins'] );

	unset( $plugins->plugins[plugin_basename(__FILE__)] );
	unset( $plugins->active[array_search( plugin_basename(__FILE__), $plugins->active )] );

	$r['body']['plugins'] = serialize( $plugins );

	return $r;

}
add_filter( 'http_request_args', 'joeuploadspurie_do_not_update_plugin_wp', 5, 2 );
