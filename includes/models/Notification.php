<?php
/**
 * Notification.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Models;

class Notification {

	public $id;

	public $remote_id = null;

	public $title;

	public $content;

	public $type;

	public $start = null;

	public $end = null;

	/**
	 * @var bool Whether the notification has been seen by the user.
	 */
	public $viewed = false;

	public $dismissed = false;

	public $date_created;

	public $date_updated;

	protected $casts = array(
		'id'        => 'int',
		'remote_id' => 'int',
		'dismissed' => 'bool',
	);

	/**
	 * Constructor
	 *
	 * @param array $data Row from the database.
	 */
	public function __construct( $data = array() ) {
		foreach ( $data as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->{$property} = $this->castAttribute( $property, $value );
			}
		}
	}

	/**
	 * Casts a property to its designated type.
	 *
	 * @todo Move to trait or base class.
	 *
	 * @param string $propertyName
	 * @param mixed  $value
	 *
	 * @return bool|float|int|mixed|string|null
	 */
	private function castAttribute( $propertyName, $value ) {
		if ( ! array_key_exists( $propertyName, $this->casts ) ) {
			return $value;
		}

		// Let null be null.
		if ( is_null( $value ) ) {
			return null;
		}

		switch ( $this->casts[ $propertyName ] ) {
			case 'array' :
				return json_decode( $value, true );
			case 'bool' :
				return (bool) $value;
			case 'float' :
				return (float) $value;
			case 'int' :
				return (int) $value;
			case 'string' :
				return (string) $value;
			default :
				return $value;
		}
	}

	/**
	 * Returns the icon name to use for this notification type.
	 *
	 * @return string
	 */
	public function getIcon() {
		switch ( $this->type ) {
			case 'warning' :
				return 'warning';
			case 'error' :
				return 'dismiss';
			case 'info' :
				return 'admin-generic';
			case 'success' :
			default :
				return 'yes-alt';
		}
	}

	/**
	 * @todo There should be a trait for this.
	 */
	public function toArray() {
		$data              = get_object_vars( $this );
		$data['icon_name'] = $this->getIcon();

		return $data;
	}

}
