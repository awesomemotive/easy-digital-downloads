<?php
/**
 * Notification.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Models;

class Notification {

	/**
	 * @var int Unique internal ID.
	 */
	public $id;

	/**
	 * @var null|int ID from the remote feed. If `null` then this notification was added internally
	 *               and not via the remote import.
	 */
	public $remote_id = null;

	/**
	 * @var string Title of the notification.
	 */
	public $title;

	/**
	 * @var string Notification content.
	 */
	public $content;

	/**
	 * @var array|null Button information, if set.
	 */
	public $buttons;

	/**
	 * @var string Notification type, including: `warning`, `error`, `info`, or `success`.
	 */
	public $type;

	/**
	 * @var array|null Conditions that must be met to display this notification. If `null`
	 *                 then there are no conditions.
	 */
	public $conditions = null;

	/**
	 * @var null|string Date to start displaying the notification.
	 */
	public $start = null;

	/**
	 * @var null|string Date to stop displaying the notification.
	 */
	public $end = null;

	/**
	 * @var bool Whether this notification has been dismissed by the user.
	 */
	public $dismissed = false;

	/**
	 * @var string Date the notification was added to the database.
	 */
	public $date_created;

	/**
	 * @var string Date the notification was last updated in the database.
	 */
	public $date_updated;

	/**
	 * @var string[]
	 */
	protected $casts = array(
		'id'         => 'int',
		'remote_id'  => 'int',
		'buttons'    => 'array',
		'conditions' => 'array',
		'dismissed'  => 'bool',
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
	 * @todo  Move to trait or base class.
	 *
	 * @since 2.11.4
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
	 * @since 2.11.4
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
	 * Converts this model to an array.
	 *
	 * @todo  Move to trait.
	 *
	 * @since 2.11.4
	 *
	 * @return array
	 */
	public function toArray() {
		$data = array();

		/*
		 * get_object_vars() returns non-public properties when used within the class
		 * so we're using a ReflectionClass to get the public properties only.
		 */
		$object = new \ReflectionClass( $this );

		foreach ( $object->getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
			if ( $property instanceof \ReflectionProperty && isset( $this->{$property->name} ) ) {
				$data[ $property->name ] = $this->{$property->name};
			}
		}

		$data['icon_name'] = $this->getIcon();

		/* Translators: %s - a length of time (e.g. "1 second") */
		$data['relative_date'] = sprintf( __( '%s ago', 'easy-digital-downloads' ), human_time_diff( strtotime( $this->date_updated ) ) );

		return $data;
	}

}
