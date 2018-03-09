<?php
/**
 * Plugin Name:     Http2 Server Push
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Takayuki Miyauchi
 * Author URI:      https://tarosky.co.jp/
 * Text Domain:     http2-server-push
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Http2_Server_Push
 */

// Autoload
require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

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
		if ( headers_sent() ) {
			return;
		}
		do_action( 'wp_enqueue_scripts' );
		send_http2_link_header( array( 'style' => wp_styles(), 'script' => wp_scripts() ) );
	}
} );

add_action( 'admin_enqueue_scripts', function() {
	if ( headers_sent() ) {
		return;
	}
	send_http2_link_header( array( 'style' => wp_styles(), 'script' => wp_scripts() ) );
}, 9999 );

function send_http2_link_header( $items ) {
	foreach( $items as $as => $wp_links ) {
		$link = '';
		foreach ( $wp_links->queue as $handle ) {
			if ( $wp_links->registered[ $handle ] && ! preg_match( '/-ie8$/i', $handle ) && 'html5' !== $handle ) {
				$wp_link = $wp_links->registered[ $handle ];
				if ( ! is_string( $wp_link->src ) ) {
					continue;
				}
				$src = preg_replace('#^https?://[^/]+#', '', $wp_link->src );
				$ver = $wp_link->ver ? $wp_link->ver : $wp_links->default_version;
				$link .= ! empty( $link ) ? ', ' : '';
				$link .= " <{$src}?ver={$ver}>; rel=preload; as={$as}";
			}
		}
		if ( ! empty( $link ) ) {
			header( "Link: $link", false );
		}
	}
}

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
