<?php

namespace Http2_Server_Push;

function send_http2_link_header( $items ) {
	$items = apply_filters( 'http2_server_push_items', $items );
	foreach( $items as $as => $urls ) {
		foreach ( $urls as $url ) {
			$link = sprintf(
				'<%s>; rel=preload; as=%s',
				esc_url( $url ),
				$as
			);
			header( "Link: " . $link, false );
		}
	}
}

/**
 * Get the URLs array from the \WP_Styles.
 *
 * @return array An array of URLs.
 */
function get_enqueued_items() {
	return array(
		'style' => get_urls( wp_styles() ),
		'script' => get_urls( wp_scripts() ),
	);
}

/**
 * @param $wp_links \WP_Scripts|\WP_Styles
 *
 * @return array
 */
function get_urls( $wp_links ) {
	$links = $wp_links->registered;
	$queue = $wp_links->queue;
	$default_version = $wp_links->default_version;

	$wp_links->all_deps( $queue );
	$to_do = $wp_links->to_do; // All list of items be loaded.

	$host_name = parse_url( home_url(), PHP_URL_HOST );

	$urls = array();
	foreach ( $links as $handle => $meta ) {
		if ( in_array( $handle, $to_do ) && is_string( $meta->src ) ) {
			$src = $meta->src;
			if ( preg_match( "#^http://#", $src ) ) {
				continue;
			} elseif ( preg_match( "#^https://#", $src )  ) {
				if ( 0 === strpos( $src, 'https://' . $host_name ) ) {
					$src = preg_replace( '#https://' . $host_name . '#', '', $src );
				} else {
					continue; // Out of the host.
				}
			}
			if ( $meta->ver ) {
				$version = $meta->ver;
			} else {
				$version = $default_version;
			}
			$urls[] = $src . '?ver=' . $version;
		}
	}

	return array_unique( $urls );
}
