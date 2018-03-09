<?php

namespace Http2_Server_Push;

function send_http2_link_header( $items ) {
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

function get_urls( $wp_links ) {
	$links = $wp_links->registered;
	$queue = $wp_links->queue;
	$default_version = $wp_links->default_version;

	$urls = array();
	foreach ( $links as $handle => $meta ) {
		if ( in_array( $handle, $queue ) && is_string( $meta->src ) ) {
			if ( $meta->ver ) {
				$version = $meta->ver;
			} else {
				$version = $default_version;
			}
			$urls[] = $meta->src . '?ver=' . $version;
		}
	}

	return $urls;
}
