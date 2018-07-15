<?php
/**
 * Add Order Page.
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Bail if incorrect view.
if ( ! isset( $_GET['view'] ) || 'add-order' !== $_GET['view'] ) {
	wp_die( esc_html__( 'Something went wrong.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}