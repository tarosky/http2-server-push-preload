<?php
/**
 * Plugin Name:     Http2 Server Push
 * Plugin URI:      https://github.com/tarosky/http2-server-push
 * Description:     A plugin which send link headers to integrate HTTP/2 server push.
 * Author:          Takayuki Miyauchi
 * Author URI:      https://tarosky.co.jp/
 * Text Domain:     http2-server-push
 * Domain Path:     /languages
 * Version:         nightly
 *
 * @package         Http2_Server_Push
 */

// Autoload
require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
require_once( dirname( __FILE__ ) . '/lib/functions.php' );

add_action( 'init', 'activate_autoupdate' );

function activate_autoupdate() {
	$plugin_slug = plugin_basename( __FILE__ ); // e.g. `hello/hello.php`.
	$gh_user = 'tarosky';                      // The user name of GitHub.
	$gh_repo = 'http2-server-push-preload';       // The repository name of your plugin.

	// Activate automatic update.
	new Miya\WP\GH_Auto_Updater( $plugin_slug, $gh_user, $gh_repo );
}

if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
	define( 'CONCATENATE_SCRIPTS', false );
}

add_action( 'wp_enqueue_scripts', function() {
	if ( headers_sent() ) {
		return;
	}
	Http2_Server_Push\send_http2_link_header( Http2_Server_Push\get_preload_items() );
}, 9999 );

add_action( 'admin_enqueue_scripts', function() {
	if ( headers_sent() ) {
		return;
	}
	Http2_Server_Push\send_http2_link_header( Http2_Server_Push\get_preload_items() );
}, 9999 );
