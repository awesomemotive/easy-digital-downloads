<?php
namespace EDD\Tests\Privacy;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Unit tests for privacy data exporters in includes/privacy-functions.php.
 *
 * Covers: edd_register_privacy_exporters, edd_privacy_customer_record_exporter,
 * edd_privacy_billing_information_exporter, edd_privacy_file_download_log_exporter,
 * edd_privacy_api_access_log_exporter.
 */
class Exporters extends EDD_UnitTestCase {

	// --- Registration (lines 593-615) ---

	public function test_register_privacy_exporters_adds_four_exporters() {
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );

		$edd_exporters = array_filter( $exporters, function ( $e ) {
			return isset( $e['callback'] ) && is_string( $e['callback'] ) && strpos( $e['callback'], 'edd_privacy_' ) === 0;
		} );

		$this->assertCount( 4, $edd_exporters );
		$this->assertSame( 'edd_privacy_customer_record_exporter', $edd_exporters[ array_key_first( $edd_exporters ) ]['callback'] );
	}

	// --- Customer record exporter (lines 629-691) ---

	public function test_customer_record_exporter_returns_empty_when_no_customer() {
		$result = edd_privacy_customer_record_exporter( 'does-not-exist@edd.test', 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );
	}

	public function test_customer_record_exporter_returns_data_for_existing_customer() {
		$customer = parent::edd()->customer->create_and_get();

		$result = edd_privacy_customer_record_exporter( $customer->email, 1 );

		$this->assertTrue( $result['done'] );
		$this->assertNotEmpty( $result['data'] );
		$this->assertSame( 'edd-customer-record', $result['data'][0]['group_id'] );
		$this->assertSame( "edd-customer-record-{$customer->id}", $result['data'][0]['item_id'] );

		$email_point = $this->find_data_point( $result['data'][0]['data'], __( 'Primary Email', 'easy-digital-downloads' ) );
		$this->assertNotNull( $email_point );
		$this->assertSame( $customer->email, $email_point['value'] );

		parent::edd()->customer->delete( $customer->id );
	}

	public function test_customer_record_exporter_includes_agree_to_terms_and_privacy_meta() {
		$customer = parent::edd()->customer->create_and_get();
		$terms_ts = time() - 3600;
		$privacy_ts = time() - 1800;
		edd_add_customer_meta( $customer->id, 'agree_to_terms_time', $terms_ts );
		edd_add_customer_meta( $customer->id, 'agree_to_privacy_time', $privacy_ts );

		$result = edd_privacy_customer_record_exporter( $customer->email, 1 );

		$this->assertTrue( $result['done'] );
		$this->assertNotEmpty( $result['data'] );

		$terms_point = $this->find_data_point( $result['data'][0]['data'], __( 'Agreed to Terms', 'easy-digital-downloads' ) );
		$this->assertNotNull( $terms_point );
		$this->assertNotEmpty( $terms_point['value'] );

		$privacy_point = $this->find_data_point( $result['data'][0]['data'], __( 'Agreed to Privacy Policy', 'easy-digital-downloads' ) );
		$this->assertNotNull( $privacy_point );
		$this->assertNotEmpty( $privacy_point['value'] );

		parent::edd()->customer->delete( $customer->id );
	}

	// --- Billing information exporter (lines 706-869) ---

	public function test_billing_information_exporter_returns_empty_when_no_customer() {
		$result = edd_privacy_billing_information_exporter( 'no-customer@edd.test', 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );
	}

	public function test_billing_information_exporter_returns_empty_when_customer_has_no_orders() {
		$customer = parent::edd()->customer->create_and_get();

		$result = edd_privacy_billing_information_exporter( $customer->email, 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );

		parent::edd()->customer->delete( $customer->id );
	}

	public function test_billing_information_exporter_returns_order_details_when_customer_has_orders() {
		$customer = parent::edd()->customer->create_and_get();
		$order    = parent::edd()->order->create_and_get(
			array( 'customer_id' => $customer->id, 'email' => $customer->email )
		);

		$result = edd_privacy_billing_information_exporter( $customer->email, 1 );

		$this->assertFalse( $result['done'] );
		$this->assertNotEmpty( $result['data'] );
		$this->assertSame( 'edd-order-details', $result['data'][0]['group_id'] );
		$this->assertSame( "edd-order-details-{$order->id}", $result['data'][0]['item_id'] );

		$order_items_point = $this->find_data_point( $result['data'][0]['data'], __( 'Order Items', 'easy-digital-downloads' ) );
		$this->assertNotNull( $order_items_point );
		$this->assertStringContainsString( 'Simple Download', (string) $order_items_point['value'] );

		parent::edd()->order->delete( $order->id );
		parent::edd()->customer->delete( $customer->id );
	}

	public function test_billing_information_exporter_does_not_fatal_when_order_item_product_deleted() {
		$customer   = parent::edd()->customer->create_and_get();
		$download_id = $this->create_download( 'Download To Delete' );

		$order = parent::edd()->order->create_and_get(
			array(
				'customer_id' => $customer->id,
				'email'       => $customer->email,
			)
		);

		$order_items = edd_get_order_items( array( 'order_id' => $order->id, 'number' => 1 ) );
		$this->assertNotEmpty( $order_items );

		edd_update_order_item(
			$order_items[0]->id,
			array(
				'product_id'   => $download_id,
				'product_name' => get_the_title( $download_id ),
			)
		);

		wp_delete_post( $download_id, true );

		$result = edd_privacy_billing_information_exporter( $customer->email, 1 );

		$this->assertFalse( $result['done'] );
		$this->assertNotEmpty( $result['data'] );
		$this->assertSame( 'edd-order-details', $result['data'][0]['group_id'] );

		$order_items_point = $this->find_data_point( $result['data'][0]['data'], __( 'Order Items', 'easy-digital-downloads' ) );
		$this->assertNotNull( $order_items_point );
		$this->assertStringContainsString( 'Download To Delete', (string) $order_items_point['value'] );

		parent::edd()->order->delete( $order->id );
		parent::edd()->customer->delete( $customer->id );
	}

	// --- File download log exporter (lines 883-962) ---

	public function test_file_download_log_exporter_returns_empty_when_no_customer() {
		$result = edd_privacy_file_download_log_exporter( 'no-customer@edd.test', 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );
	}

	public function test_file_download_log_exporter_returns_empty_when_customer_has_no_logs() {
		$customer = parent::edd()->customer->create_and_get();

		$result = edd_privacy_file_download_log_exporter( $customer->email, 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );

		parent::edd()->customer->delete( $customer->id );
	}

	public function test_file_download_log_exporter_returns_log_with_file_name_meta() {
		$customer = parent::edd()->customer->create_and_get();
		$order    = parent::edd()->order->create_and_get(
			array( 'customer_id' => $customer->id, 'email' => $customer->email )
		);

		$log = parent::edd()->file_download_log->create_and_get(
			array(
				'customer_id' => $customer->id,
				'product_id'  => 1,
				'order_id'    => $order->id,
				'file_id'     => 1,
			)
		);
		edd_add_file_download_log_meta( $log->id, 'file_name', 'exported-file.zip' );

		$result = edd_privacy_file_download_log_exporter( $customer->email, 1 );

		$this->assertFalse( $result['done'] );
		$this->assertNotEmpty( $result['data'] );
		$this->assertSame( 'edd-file-download-logs', $result['data'][0]['group_id'] );

		$file_name_point = $this->find_data_point( $result['data'][0]['data'], __( 'File Name', 'easy-digital-downloads' ) );
		$this->assertNotNull( $file_name_point );
		$this->assertSame( 'exported-file.zip', $file_name_point['value'] );

		parent::edd()->file_download_log->delete( $log->id );
		parent::edd()->order->delete( $order->id );
		parent::edd()->customer->delete( $customer->id );
	}

	public function test_file_download_log_exporter_returns_na_when_file_name_meta_missing() {
		$customer = parent::edd()->customer->create_and_get();
		$order    = parent::edd()->order->create_and_get(
			array( 'customer_id' => $customer->id, 'email' => $customer->email )
		);

		$log = parent::edd()->file_download_log->create_and_get(
			array(
				'customer_id' => $customer->id,
				'product_id'  => 1,
				'order_id'    => $order->id,
				'file_id'     => 1,
			)
		);

		$result = edd_privacy_file_download_log_exporter( $customer->email, 1 );

		$this->assertFalse( $result['done'] );
		$this->assertNotEmpty( $result['data'] );

		$file_name_point = $this->find_data_point( $result['data'][0]['data'], __( 'File Name', 'easy-digital-downloads' ) );
		$this->assertNotNull( $file_name_point );
		$this->assertSame( __( 'N/A', 'easy-digital-downloads' ), $file_name_point['value'] );

		parent::edd()->file_download_log->delete( $log->id );
		parent::edd()->order->delete( $order->id );
		parent::edd()->customer->delete( $customer->id );
	}

	public function test_file_download_log_exporter_does_not_fatal_when_product_deleted() {
		$customer    = parent::edd()->customer->create_and_get();
		$customer    = parent::edd()->customer->get_object_by_id( $customer->id );
		$download_id = $this->create_download( 'Download To Delete (Log)' );
		$order       = parent::edd()->order->create_and_get(
			array( 'customer_id' => $customer->id, 'email' => $customer->email )
		);

		$log = parent::edd()->file_download_log->create_and_get(
			array(
				'customer_id' => $customer->id,
				'product_id'  => $download_id,
				'order_id'    => $order->id,
				'file_id'     => 1,
			)
		);
		edd_add_file_download_log_meta( $log->id, 'file_name', 'file-to-export.zip' );

		wp_delete_post( $download_id, true );

		$result = edd_privacy_file_download_log_exporter( $customer->email, 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'data', $result );
		$this->assertArrayHasKey( 'done', $result );
		if ( ! empty( $result['data'] ) ) {
			$this->assertFalse( $result['done'], 'Exporter should return done=false when logs exist' );
			$file_name_point = $this->find_data_point( $result['data'][0]['data'], __( 'File Name', 'easy-digital-downloads' ) );
			$this->assertNotNull( $file_name_point );
			$this->assertSame( 'file-to-export.zip', $file_name_point['value'] );
		}

		parent::edd()->file_download_log->delete( $log->id );
		parent::edd()->order->delete( $order->id );
		parent::edd()->customer->delete( $customer->id );
	}

	// --- API access log exporter (lines 975-1043) ---

	public function test_api_access_log_exporter_returns_empty_when_no_user() {
		$result = edd_privacy_api_access_log_exporter( 'no-user@edd.test', 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );
	}

	public function test_api_access_log_exporter_returns_empty_when_user_has_no_logs() {
		$user = self::factory()->user->create_and_get(
			array( 'user_email' => 'nologs@edd.test', 'user_login' => 'nologs' )
		);

		$result = edd_privacy_api_access_log_exporter( $user->user_email, 1 );

		$this->assertTrue( $result['done'] );
		$this->assertSame( array(), $result['data'] );

		wp_delete_user( $user->ID );
	}

	public function test_api_access_log_exporter_returns_logs_for_user() {
		// Use a unique email and login so no leftover user from another test can be
		// returned by get_user_by('email'), which would make the exporter look up
		// logs for the wrong user and return done=>true (no logs).
		$unique     = 'api-export-' . uniqid();
		$user_email = $unique . '@edd.test';
		$user       = self::factory()->user->create_and_get(
			array( 'user_email' => $user_email, 'user_login' => $unique )
		);

		// Ensure API request logging is allowed (another test may have disabled it).
		add_filter( 'edd_should_log_api_request', '__return_true', 999 );
		// Bypass the factory and insert the log directly so behavior is consistent
		// when running alone vs full suite (factory merge can differ by PHP/suite order).
		$request_value = 'edd-api=sales&key=test&token=test';
		$log_id        = edd_add_api_request_log(
			array(
				'user_id' => $user->ID,
				'request' => $request_value,
				'api_key' => 'test-key',
				'token'   => 'test-token',
				'ip'      => '127.0.0.1',
			)
		);
		remove_filter( 'edd_should_log_api_request', '__return_true', 999 );
		$this->assertNotFalse( $log_id, 'API request log must be created for this test.' );
		$log = edd_get_api_request_log( $log_id );
		$this->assertInstanceOf( 'EDD\Logs\Api_Request_Log', $log );

		$result = edd_privacy_api_access_log_exporter( $user_email, 1 );

		$this->assertFalse( $result['done'], 'Exporter should return done=false when the user has API logs.' );
		$this->assertNotEmpty( $result['data'] );
		$this->assertSame( 'edd-api-access-logs', $result['data'][0]['group_id'] );

		$request_point = $this->find_data_point( $result['data'][0]['data'], __( 'Request', 'easy-digital-downloads' ) );
		$this->assertNotNull( $request_point );
		$this->assertSame( $request_value, $request_point['value'] );

		edd_delete_api_request_log( $log_id );
		wp_delete_user( $user->ID );
	}

	/**
	 * Create a downloadable product (EDD "download" post type).
	 *
	 * @param string $title Post title.
	 * @return int Post ID.
	 */
	protected function create_download( $title = 'Test Download' ) {
		return self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => $title,
			)
		);
	}

	/**
	 * Find a data point by its translated name.
	 *
	 * @param array  $data_points Array of name/value data points.
	 * @param string $name        Data point name to find.
	 * @return array|null Data point or null.
	 */
	protected function find_data_point( $data_points, $name ) {
		foreach ( (array) $data_points as $data_point ) {
			if ( isset( $data_point['name'] ) && $name === $data_point['name'] ) {
				return $data_point;
			}
		}
		return null;
	}
}
