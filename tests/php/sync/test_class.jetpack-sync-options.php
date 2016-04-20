<?php

require_once dirname( __FILE__ ) . '/../../../sync/class.jetpack-sync-options.php';

/**
 * Testing CRUD on Options
 */
class WP_Test_Jetpack_New_Sync_Options extends WP_Test_Jetpack_New_Sync_Base {
	protected $post;

	public function setUp() {
		parent::setUp();

		$this->client->set_options_whitelist( array( 'test_option' ) );

		add_option( 'test_option', 'foo' );

		$this->client->do_sync();
	}

	public function test_added_option_is_synced() {
		$synced_option_value = $this->server_replica_storage->get_option( 'test_option' );
		$this->assertEquals( 'foo', $synced_option_value );
	}

	public function test_updated_option_is_synced() {
		update_option( 'test_option', 'bar' );
		$this->client->do_sync();
		$synced_option_value = $this->server_replica_storage->get_option( 'test_option' );
		$this->assertEquals( 'bar', $synced_option_value );
	}

	public function test_deleted_option_is_synced() {
		delete_option( 'test_option' );
		$this->client->do_sync();
		$synced_option_value = $this->server_replica_storage->get_option( 'test_option' );
		$this->assertEquals( false, $synced_option_value );
	}

	public function test_don_t_sync_option_if_not_on_whitelist() {
		add_option( 'don_t_sync_test_option', 'foo' );
		$this->client->do_sync();
		$synced_option_value = $this->server_replica_storage->get_option( 'don_t_sync_test_option' );
		$this->assertEquals( false, $synced_option_value );
	}

}

// phpunit --testsuite sync
//class WP_Test_Jetpack_Sync_Options extends WP_UnitTestCase {
//
//	public function setUp() {
//		parent::setUp();
//
//		Jetpack_Sync_Options::init();
//		self::reset_sync();
//
//		// Set the current user to user_id 1 which is equal to admin.
//		wp_set_current_user( 1 );
//	}
//
//	public function tearDown() {
//		parent::tearDown();
//
//	}
//
//	public function test_sync_add_new_option() {
//		$option = 'new_option_0';
//		Jetpack_Sync_Options::register( $option );
//
//		add_option( $option, 1 );
//
//		$this->assertContains( $option, array_keys( Jetpack_Sync_Options::get_to_sync() ) );
//		$this->assertTrue( Jetpack_Sync::$do_shutdown );
//	}
//
//	public function test_sync_update_option() {
//		$option = 'new_option_1';
//		Jetpack_Sync_Options::register( $option );
//		add_option( $option, 1 );
//
//		self::reset_sync();
//		update_option( $option, 2 );
//
//		$this->assertContains( $option, array_keys( Jetpack_Sync_Options::get_to_sync() ) );
//		$this->assertTrue( Jetpack_Sync::$do_shutdown );
//	}
//
//	public function test_sync_delete_option() {
//		$option = 'new_option_2';
//		Jetpack_Sync_Options::register( $option );
//		add_option( $option, 1 );
//
//		self::reset_sync();
//		delete_option( $option );
//
//		$this->assertContains( $option, Jetpack_Sync_Options::get_to_delete() );
//		$this->assertTrue( Jetpack_Sync::$do_shutdown );
//	}
//
//	public function test_sync_updated_option() {
//		$first_option = Jetpack_Sync_Options::$options[0];
//		$new_blogname = 'updated first option';
//
//		update_option( $first_option, $new_blogname );
//		$data_to_sync = Jetpack_Sync_Options::get_to_sync();
//
//		$this->assertContains( $new_blogname, $data_to_sync );
//
//	}
//
//	public function test_sync_core_site_icon_add() {
//		$filename = dirname( __FILE__ ) . '/../files/jetpack.jpg';
//
//		// Check the type of file. We'll use this as the 'post_mime_type'.
//		$filetype = wp_check_filetype( basename( $filename ), null );
//
//		// Get the path to the upload directory.
//		$wp_upload_dir = wp_upload_dir();
//
//		// Prepare an array of post data for the attachment.
//		$attachment = array(
//			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
//			'post_mime_type' => $filetype['type'],
//			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
//			'post_content'   => '',
//			'post_status'    => 'inherit'
//		);
//
//		// Insert the attachment.
//		$attach_id = wp_insert_attachment( $attachment, $filename );
//		if ( function_exists( 'get_site_icon_url' ) ) {
//			update_option( 'site_icon', $attach_id );
//		} else {
//			Jetpack_Options::update_option( 'site_icon_id', $attach_id );
//		}
//
//		$data_to_sync = Jetpack_Sync_Options::get_to_sync();
//		if ( function_exists( 'get_site_icon_url' ) ) {
//			$this->assertEquals( $attach_id, $data_to_sync['site_icon'] );
//			$this->assertEquals( get_site_icon_url(), $data_to_sync['jetpack_site_icon_url'] );
//		} else {
//			$this->assertEquals( jetpack_site_icon_url(), $data_to_sync['jetpack_site_icon_url'] );
//		}
//	}
//
//	public function test_sync_first_option_all() {
//		$first_option = Jetpack_Sync_Options::$options[0];
//		$new_blogname = get_option( $first_option );
//
//		$data_to_sync = Jetpack_Sync_Options::get_all();
//		$this->assertContains( $new_blogname, $data_to_sync );
//	}
//
//	private function reset_sync() {
//		Jetpack_Sync_Options::$sync   = array();
//		Jetpack_Sync_Options::$delete = array();
//		Jetpack_Sync::$do_shutdown    = false;
//	}
//}