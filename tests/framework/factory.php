<?php
class EDD_UnitTest_Factory extends WP_UnitTest_Factory {
	public function __construct() {
		parent::_construct();

		$this->download = new EDD_UnitTest_Factory_For_Download( $this );
		$this->payment = new EDD_UnitTest_Factory_For_Payment( $this );
		$this->discount = new EDD_UnitTest_Factory_For_Discount( $this );
	}
}

class EDD_UnitTest_Factory_For_Download extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
	}
}

class EDD_UnitTest_Factory_For_Payment extends WP_UnitTest_Factory_For_Thing {

}

class EDD_UnitTest_Factory_For_Discount extends WP_UnitTest_Factory_For_Thing {
	
}