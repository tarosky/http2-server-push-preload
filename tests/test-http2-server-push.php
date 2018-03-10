<?php

class Http2_Server_Push_Test extends WP_UnitTestCase
{
	public function test_get_enqueued_items()
	{
		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style( 'test', 'test.css', false, 123 );
			wp_enqueue_script( 'test', 'test.js', array( 'jquery' ), 456 );
		} );

		do_action( 'wp_enqueue_scripts' );

		$urls = Http2_Server_Push\get_enqueued_items();

		$this->assertTrue( is_array( $urls ) );
		$this->assertTrue( is_array( $urls['style'] ) );
		$this->assertTrue( is_array( $urls['script'] ) );
		$this->assertSame( 1, count( $urls['style'] ) );
		$this->assertTrue( 3 === count( $urls['script'] ) ); // It contains jquery.
		$this->assertSame( "test.css?ver=123", $urls['style'][0] );
		$this->assertTrue( in_array( "test.js?ver=456", $urls['script'] ) );
	}

	public function test_get_urls()
	{
		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_script( 'test', 'test.js', array( 'jquery' ), 456 );
		} );

		do_action( 'wp_enqueue_scripts' );

		$urls = Http2_Server_Push\get_urls( wp_scripts() );

		$this->assertTrue( is_array( $urls ) );
		$this->assertTrue( 3 === count( $urls ) ); // It contains jquery.
		$this->assertTrue( $this->is_contain( "/jquery\.js\?ver\=/", $urls ) );
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
