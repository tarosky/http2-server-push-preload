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
	$gh_repo = 'http2-server-push';       // The repository name of your plugin.

	// Activate automatic update.
	new Miya\WP\GH_Auto_Updater( $plugin_slug, $gh_user, $gh_repo );
}

if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
	define( 'CONCATENATE_SCRIPTS', false );
}

add_action( 'send_headers', function() {
	if ( ! is_admin() ) {
		do_action( 'wp_enqueue_scripts' );
		Http2_Server_Push\send_http2_link_header( Http2_Server_Push\get_enqueued_items() );
	}
} );

add_action( 'admin_enqueue_scripts', function() {
	if ( headers_sent() ) {
		return;
	}
	Http2_Server_Push\send_http2_link_header( Http2_Server_Push\get_enqueued_items() );
}, 9999 );

add_filter( 'http_request_args', function ( $response, $url ) {
	if ( 0 === strpos( $url, 'https://api.wordpress.org/plugins/update-check' ) ) {
		$basename = plugin_basename( __FILE__ );
		$plugins  = json_decode( $response['body']['plugins'] );
		unset( $plugins->plugins->$basename );
		unset( $plugins->active[ array_search( $basename, $plugins->active ) ] );
		$response['body']['plugins'] = json_encode( $plugins );
	}
	return $response;
}, 10, 2 );
