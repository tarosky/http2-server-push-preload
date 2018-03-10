<?php

class Http2_Server_Push_Test extends WP_UnitTestCase
{
	public function setUp()
	{
		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style( 'test', 'https://example.org/test.css', false, 123 );
			wp_enqueue_script( 'test-1', 'https://example.org/test1.js', array( 'jquery' ), 456 );
			wp_enqueue_script( 'test-2', 'https://example.com/test2.js', array( 'jquery' ), 456 );
			wp_enqueue_script( 'test-3', 'http://example.com/test3.js', array( 'jquery' ), 456 );
		} );

		do_action( 'wp_enqueue_scripts' );
	}

	public function test_get_enqueued_items()
	{
		$urls = Http2_Server_Push\get_enqueued_items();

		$this->assertTrue( is_array( $urls ) );
		$this->assertTrue( is_array( $urls['style'] ) );
		$this->assertTrue( is_array( $urls['script'] ) );
		$this->assertSame( 1, count( $urls['style'] ) );
		$this->assertTrue( 3 === count( $urls['script'] ) ); // It contains jquery.
		$this->assertSame( "/test.css?ver=123", $urls['style'][0] );
		$this->assertTrue( in_array( "/test1.js?ver=456", $urls['script'] ) );
	}

	public function test_get_urls()
	{
		$urls = Http2_Server_Push\get_urls( wp_scripts() );

		$this->assertTrue( is_array( $urls ) );
		$this->assertTrue( 3 === count( $urls ) ); // It contains jquery.
		$this->assertTrue( $this->is_contain( "/jquery\.js\?ver\=/", $urls ) );

		// Followings should not match.
		$this->assertFalse( $this->is_contain( "#https?://#", $urls ) );
		$this->assertFalse( $this->is_contain( "#example.com#", $urls ) );
	}

	public function test_filter_http2_server_push_items()
	{
		add_filter( 'http2_server_push_items', function( $items ) {
			$new = array(
				'image' => array(
					'/wp-content/uploads/favicon.png'
				),
			);

			return array_merge_recursive( $items, $new );
		} );

		$urls = Http2_Server_Push\get_enqueued_items();

		$this->assertSame( '/wp-content/uploads/favicon.png', $urls['image'][0] );
		$this->assertTrue( !! $urls['style'] );
	}

	public function test_array_merge_recursive()
	{
		$items = array(
			'style' => array(
				'test1.css',
				'test2.css'
			),
			'script' => array(
				'test1.js',
				'test2.js',
			),
		);

		$result = array_merge_recursive( $items, array(
			'style' => array(
				'test3.css',
			),
			'image' => array(
				'test1.jpg',
				'test2.jpg',
			),
		) );

		$expect = array(
			'style' => array(
				'test1.css',
				'test2.css',
				'test3.css',
			),
			'script' => array(
				'test1.js',
				'test2.js',
			),
			'image' => array(
				'test1.jpg',
				'test2.jpg',
			),
		);

		$this->assertSame( $expect, $result );
	}

	/**
	 * @param $regex string The regex to searched.
	 * @param $array array An array to search.
	 *
	 * @return bool
	 */
	private function is_contain( $regex, $array )
	{
		foreach ( $array as $str ) {
			if ( preg_match( $regex, $str ) ) {
				$this->assertTrue( true );
				return true;
			}
		}

		return false;
	}
}
