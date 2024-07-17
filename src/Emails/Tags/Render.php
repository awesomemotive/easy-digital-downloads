<?php
/**
 * Render email tags.
 *
 * @since 3.3.0
 * @package EDD
 * @subpackage Emails\Tags
 */

namespace EDD\Emails\Tags;

use EDD\Orders\Order;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Render
 *
 * @since 3.3.0
 */
class Render {

	/**
	 * Renders the login link email tag.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function login_link() {
		if ( 'text/plain' === EDD()->emails->get_content_type() ) {
			return wp_login_url();
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_login_url(),
			__( 'Login', 'easy-digital-downloads' )
		);
	}

	/**
	 * Email tag callback for {password_link}.
	 * Returns the link for new users; otherwise returns an empty string.
	 *
	 * @since 3.3.0
	 * @param int    $object_id    The object ID.
	 * @param mixed  $email_object The email object.
	 * @param string $context      The context.
	 * @return string
	 */
	public function password_link( $object_id, $email_object = null, $context = 'order' ) {
		$context = EDD()->email_tags->get_context( $context );
		if ( 'order' === $context && ! $email_object instanceof Order ) {
			$email_object = edd_get_order( $object_id );
		}
		$user = false;
		if ( 'order' === $context ) {
			if ( ! $email_object instanceof Order ) {
				return '';
			}
			if ( ! $this->is_first_purchase( $email_object->user_id ) ) {
				return '';
			}
			$user = $this->get_user_data( $email_object->user_id );
			if ( ! $user ) {
				return '';
			}
		} elseif ( 'user' === $context ) {
			if ( $email_object instanceof \WP_User ) {
				$user = $email_object;
			} else {
				$user = $this->get_user_data( $object_id );
			}
		}
		if ( ! $user ) {
			return '';
		}
		$password_reset_link = edd_get_password_reset_link( $user );
		if ( ! $password_reset_link ) {
			return '';
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			$password_reset_link,
			__( 'Set your password', 'easy-digital-downloads' )
		);
	}

	/**
	 * Renders the refund link email tag.
	 *
	 * @since 3.3.0
	 * @param int                            $refund_id Refund ID.
	 * @param Order                          $refund    Refund object.
	 * @param string|\EDD\Emails\Types\Email $context_or_email   Context or email object.
	 * @return string
	 */
	public function refund_link( $refund_id, $refund = null, $context_or_email = '' ) {
		if ( $context_or_email instanceof \EDD\Emails\Types\Email ) {
			if ( 'admin' !== $context_or_email->recipient_type ) {
				return '';
			}
			$context = $context_or_email->get_context();
		} else {
			$context = $context_or_email;
		}
		if ( 'refund' !== $context || empty( $refund ) || 'refund' !== $refund->type ) {
			return '{refund_link}';
		}

		return edd_get_admin_url(
			array(
				'page' => 'edd-payment-history',
				'view' => 'view-refund-details',
				'id'   => absint( $refund_id ),
			)
		);
	}

	/**
	 * Renders the order details link email tag.
	 *
	 * @since 3.3.0
	 * @param int    $order_id Order ID.
	 * @param Order  $order    Order object.
	 * @param string $context  The context.
	 *
	 * @return string
	 */
	public function order_details_link( $order_id, $order = null, $context = '' ) {
		if ( $context instanceof \EDD\Emails\Types\Email ) {
			if ( 'admin' !== $context->recipient_type ) {
				return '';
			}
			$context = $context->get_context();
		}
		if ( 'order' !== $context ) {
			return '{order_details_link}';
		}

		// If the order object is not a valid order, get it.
		if ( ! $order ) {
			$order = edd_get_order( $order_id );
		}

		if ( ! $order ) {
			return '{order_details_link}';
		}

		return edd_get_admin_url(
			array(
				'page' => 'edd-payment-history',
				'view' => 'view-order-details',
				'id'   => absint( $order_id ),
			)
		);
	}

	/**
	 * Renders the transaction ID email tag.
	 *
	 * @since 3.3.0
	 *
	 * @param int    $order_id Order ID.
	 * @param object $order    Order object.
	 * @param string $context  Context.
	 * @return string
	 */
	public function transaction_id( $order_id, $order = null, $context = 'order' ) {
		if ( $context instanceof \EDD\Emails\Types\Email ) {
			if ( 'admin' !== $context->recipient_type ) {
				return '';
			}
			$context = $context->get_context();
		}
		if ( 'order' !== $context ) {
			return '';
		}

		if ( empty( $order ) || ! $order instanceof Order ) {
			$order = edd_get_order( $order_id );
		}

		// phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
		return apply_filters( 'edd_payment_details_transaction_id-' . $order->gateway, $order->get_transaction_id(), $order->id );
	}

	/**
	 * Renders the fees total email tag.
	 *
	 * @since 3.3.0
	 *
	 * @param int    $order_id Order ID.
	 * @param object $order    Order object.
	 * @return string
	 */
	public function fees_total( $order_id, $order = null ) {
		if ( ! $order instanceof Order ) {
			$order = edd_get_order( $order_id );
		}

		$total = array_reduce(
			$order ? $order->get_fees() : array(),
			function ( $carry, $fee ) {
				return $carry + $fee->get_amount();
			},
			0
		);

		return edd_currency_filter( edd_format_amount( $total ), $order->currency );
	}

	/**
	 * Renders the fees list email tag.
	 *
	 * @since 3.3.0
	 *
	 * @param int    $order_id Order ID.
	 * @param object $order    Order object.
	 * @return string
	 */
	public function fees_list( $order_id, $order = null ) {
		if ( ! $order instanceof Order ) {
			$order = edd_get_order( $order_id );
		}

		$fees = $order ? $order->get_fees() : array();

		if ( empty( $fees ) ) {
			return '';
		}

		return sprintf(
			'<ul>%s</ul>',
			array_reduce(
				$fees,
				function ( $carry, $fee ) use ( $order ) {
					return $carry . sprintf(
						'<li>%s: %s</li>',
						$fee->description ?? __( 'Fee', 'easy-digital-downloads' ),
						edd_currency_filter( edd_format_amount( $fee->get_amount() ), $order->currency )
					);
				},
				''
			)
		);
	}

	/**
	 * Retrieves the total refund amount for a given refund.
	 *
	 * @since 3.3.0
	 * @param int                            $order_id     The ID of the order.
	 * @param object|null                    $refund_order The refund order object. Default is null.
	 * @param \EDD\Emails\Types\Email|string $context      The context of the refund. Default is null.
	 * @return float The refund amount.
	 */
	public function refund_amount( $order_id, $refund_order = null, $context = null ) {
		if ( $context instanceof \EDD\Emails\Types\Email ) {
			$context = $context->get_context();
		}
		if ( 'refund' !== $context ) {
			return '';
		}

		if ( ! $refund_order instanceof Order ) {
			$refund_order = edd_get_order( $order_id );
		}

		// If the order is not a valid refund order, return the tag to allow for the second round of parsing.
		if ( ! $refund_order instanceof Order || 'refund' !== $refund_order->type ) {
			return '{refund_amount}';
		}

		return edd_currency_filter( edd_format_amount( $refund_order->total * -1 ), $refund_order->currency );
	}

	/**
	 * Retrieves the refund ID for a given order.
	 *
	 * @since 3.3.0
	 * @param int                            $order_id     The ID of the order.
	 * @param object|null                    $refund       The refund order object. Default is null.
	 * @param \EDD\Emails\Types\Email|string $context      The context of the refund. Default is null.
	 * @return string The refund ID if found, null otherwise.
	 */
	public function refund_id( $order_id, $refund = null, $context = null ) {
		if ( $context instanceof \EDD\Emails\Types\Email ) {
			$context = $context->get_context();
		}
		if ( 'refund' !== $context ) {
			return '';
		}
		if ( is_null( $refund ) ) {
			$refund = edd_get_order( $order_id );
		}

		if ( $refund instanceof Order && 'refund' === $refund->type ) {
			return $refund->order_number;
		}

		return '{refund_id}';
	}

	/**
	 * Check if it the first purchase for a given user.
	 *
	 * @since 3.3.0
	 *
	 * @param int $user_id The user ID.
	 * @return bool
	 */
	private function is_first_purchase( $user_id ) {
		return empty(
			edd_get_orders(
				array(
					'type'       => 'sale',
					'number'     => 1,
					'status__in' => edd_get_complete_order_statuses(),
					'user_id'    => $user_id,
				)
			)
		);
	}

	/**
	 * Fetch user data.
	 *
	 * @since 3.3.0
	 * @param int $user_id The user ID.
	 * @return WP_User|false WP_User object on success, false on failure.
	 */
	private function get_user_data( $user_id = 0 ) {
		if ( $user_id ) {
			return get_userdata( $user_id );
		}

		return false;
	}
}
