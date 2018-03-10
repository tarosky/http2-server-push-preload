<?php

namespace Http2_Server_Push;

/**
 * Send the `link:` http response header.
 *
 * @param $items array
 */
function send_http2_link_header( $items ) {
	$links = array();
	foreach( $items as $as => $urls ) {
		foreach ( $urls as $url ) {
			$links[] = sprintf(
				'<%s>; rel=preload; as=%s',
				$url,
				$as
			);
		}
	}

	if ( $links ) {
		header( "Link: " . implode( ", ", array_unique( $links ) ), false );
	}
}

/**
 * Get the URLs array from the \WP_Styles.
 *
 * @return array An array of URLs.
 */
function get_preload_items() {
	$items = array(
		'style' => get_urls( wp_styles() ),
		'script' => get_urls( wp_scripts() ),
	);

	return apply_filters( 'http2_server_preload_items', $items );
}

/**
 * Get the list of url enqueued.
 *
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
	foreach ( $to_do as $handle ) {
		if ( ! empty( $links[ $handle ] ) && ! empty( $links[ $handle ]->src )
		                        && is_string( $links[ $handle ]->src ) ) {
			$src = esc_url_raw( $links[ $handle ]->src );
			if ( preg_match( "#^http://#", $src ) ) {
				continue;
			} elseif ( preg_match( "#^https://#", $src ) ) {
				if ( 0 === strpos( $src, 'https://' . $host_name ) ) {
					$src = str_replace( 'https://' . $host_name, '', $src );
				} else {
					continue; // Out of the host.
				}
			}
			if ( $links[ $handle ]->ver ) {
				$version = $links[ $handle ]->ver;
			} else {
				$version = $default_version;
			}
			$urls[] = $src . '?ver=' . $version;
		}
	}

	return array_unique( $urls );
}
