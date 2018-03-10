<?php

class Http2_Server_Push_Test extends WP_UnitTestCase
{
	public function test_get_enqueued_items()
	{
		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style( 'test', 'test.css', false, 123 );
			wp_enqueue_script( 'test', 'test.js', false, 456 );
		} );

		do_action( 'wp_enqueue_scripts' );

		$urls = Http2_Server_Push\get_enqueued_items();

		$this->assertTrue( is_array( $urls ) );
		$this->assertTrue( is_array( $urls['style'] ) );
		$this->assertTrue( is_array( $urls['script'] ) );
		$this->assertSame( 1, count( $urls['style'] ) );
		$this->assertSame( 1, count( $urls['script'] ) );
		$this->assertSame( "test.css?ver=123", $urls['style'][0] );
		$this->assertSame( "test.js?ver=456", $urls['script'][0] );
	}
}
