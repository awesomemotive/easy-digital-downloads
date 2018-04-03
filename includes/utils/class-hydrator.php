<?php
namespace EDD\Utils;

/**
 * Hydrates a target class or object.
 *
 * @since 3.0.0
 */
class Hydrator {

	/**
	 * Class map for the hydrator.
	 *
	 * @since 3.0
	 * @var   array
	 */
	private $class_map;

	/**
	 * Hydrates a target object with given data.
	 *
	 * @since 3.0
	 *
	 * @throws \ReflectionException
	 *
	 * @param string|object $target Target class or object.
	 * @param array         $data   Data to hydrate.
	 * @return object The hydrated object.
	 */
	public function hydrate( $target, $data ) {

		$reflection = $this->reflect( $target );

		if ( ! is_object( $target ) ) {
			$target = $reflection->newInstanceWithoutConstructor();
		}

		foreach ( $data as $key => $value ) {
			$property = $reflection->getProperty( $key );

			// Convert private and protected to public.
			if ( $property->isPrivate() || $property->isProtected() ) {
				$property->setAccessible( true );
			}

			$property->setValue( $target, $value );
		}

		return $target;
	}

	/**
	 * Extracts a hydrated object in array form.
	 *
	 * @since 3.0
	 *
	 * @param object       $object Hydrated object to extract.
	 * @param array|string $fields Optional. Fields to extract from the object. Accepts 'all' for
	 *                             all object fields or an array of fields. Default 'all'.
	 * @return array
	 * @throws \ReflectionException
	 */
	public function extract( $object, $fields = 'all' ) {
		$reflection = $this->reflect( $object );

		$data = array();

		if ( is_string( $fields ) ) {
			if ( 'all' === $fields ) {
				$props  = $reflection->getProperties();
				$fields = array_keys( $props );
			} else {
				$fields = array( $fields );
			}
		}

		foreach ( $fields as $key ) {
			$property = $reflection->getProperty( $key );

			// Convert private and protected to public.
			if ( $property->isPrivate() || $property->isProtected() ) {
				$property->setAccessible( true );
			}

			$data[ $property->getName() ] = $property->getValue( $object );
		}

		return $data;
	}

	/**
	 * Retrieves a reflection of the given target class or object.
	 *
	 * @since 3.0
	 *
	 * @throws \ReflectionException
	 *
	 * @param string|object $target Target class or object.
	 * @return \ReflectionClass ReflectionClass instance for the target.
	 */
	private function reflect( $target ) {

		if ( is_object( $target ) ) {
			$target = get_class( $target );
		}

		if ( ! isset( $this->class_map[ $target ] ) ) {
			$this->class_map[ $target ] = new \ReflectionClass( $target );
		}

		return $this->class_map[ $target ];
	}

}
