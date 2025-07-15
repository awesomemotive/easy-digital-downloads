<?php
/**
 * TaxRates Queries Class.
 *
 * @package     EDD\Database\Queriess
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Query;

/**
 * Class TaxRate
 *
 * @since 3.5.0
 * @package EDD\Database\Queries
 */
class TaxRate extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $table_name = 'tax_rates';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $table_alias = 'tr';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\TaxRates';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $item_name = 'tax_rate';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $item_name_plural = 'tax_rates';

	/**
	 * The shape of the item.
	 *
	 * @since 3.5.0
	 * @var mixed
	 */
	protected $item_shape = 'EDD\\Taxes\\Rate';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $cache_group = 'tax_rates';

	/**
	 * Add a tax rate.
	 *
	 * @since 3.5.0
	 *
	 * @param array $data Data to add.
	 * @return int|false Tax rate ID if successful, false otherwise.
	 */
	public function add_item( $data = array() ) {
		$data = wp_parse_args(
			$data,
			array(
				'country' => '',
				'state'   => '',
				'status'  => 'active',
				'scope'   => 'country',
				'source'  => 'manual',
			)
		);

		$fields_to_map = array(
			'name'        => 'country',
			'description' => 'state',
		);

		foreach ( $fields_to_map as $original => $new ) {
			if ( ! empty( $data[ $original ] ) ) {
				$data[ $new ] = $data[ $original ];
			}
			if ( array_key_exists( $original, $data ) ) {
				unset( $data[ $original ] );
			}
		}

		if ( ! empty( $data['state'] ) ) {
			$data['scope'] = 'region';
		} elseif ( empty( $data['state'] ) && empty( $data['country'] ) ) {
			$data['scope'] = 'global';
		}

		$tax_rate_id = parent::add_item( $data );

		$this->maybe_deactivate_existing_rate( $tax_rate_id, $data );

		return $tax_rate_id;
	}

	/**
	 * Deactivate existing tax rate if it exists.
	 *
	 * @since 3.5.0
	 * @param int   $tax_rate_id Tax rate ID.
	 * @param array $data        Data to update.
	 * @return bool Whether the tax rate was updated.
	 */
	private function maybe_deactivate_existing_rate( $tax_rate_id, $data ) {
		$data_to_check   = array(
			'fields'     => 'ids',
			'status'     => 'active',
			'country'    => $data['country'],
			'state'      => $data['state'],
			'scope'      => $data['scope'],
			'id__not_in' => array( $tax_rate_id ),
		);
		$tax_rate_exists = $this->query( $data_to_check );

		if ( empty( $tax_rate_exists ) ) {
			return false;
		}

		$tax_rate = reset( $tax_rate_exists );

		return $this->update_item( $tax_rate, array( 'status' => 'inactive' ) );
	}
}
