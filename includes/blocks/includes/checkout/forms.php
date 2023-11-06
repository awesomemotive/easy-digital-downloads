<?php
/**
 * Checkout form functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Gets the array of personal info forms for the checkout form.
 *
 * @since 2.0
 * @param array $block_attributes       The block attributes.
 * @param bool  $customer_info_complete Whether the logged in customer information is complete.
 * @return array
 */
function get_personal_info_forms( $block_attributes, $customer_info_complete = true ) {
	$forms = array();
	if ( is_user_logged_in() && $customer_info_complete ) {
		return $forms;
	}
	$options = get_forms();
	if ( ! edd_no_guest_checkout() || ( ! $customer_info_complete && is_user_logged_in() ) ) {
		$forms['guest'] = $options['guest'];
	}
	if ( ! empty( $block_attributes['show_register_form'] ) && ! is_user_logged_in() ) {
		$setting = $block_attributes['show_register_form'];
		if ( 'both' === $setting ) {
			$forms['register'] = $options['register'];
			$forms['login']    = $options['login'];
		} elseif ( 'registration' === $setting ) {
			$forms['register'] = $options['register'];
		} elseif ( ! empty( $options[ $setting ] ) ) {
			$forms[ $setting ] = $options[ $setting ];
		}
	}

	// If no forms have been set, add the registration form (guest checkout is disabled).
	if ( empty( $forms ) ) {
		$forms['register'] = $options['register'];
	}

	return $forms;
}

/**
 * Shows the login and/or registration form for guest users in checkout.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return void
 */
function do_personal_info_forms( $block_attributes ) {
	$customer               = \EDD\Blocks\Checkout\get_customer();
	$customer_info_complete = true;
	if ( is_user_logged_in() ) {
		include EDD_BLOCKS_DIR . 'views/checkout/purchase-form/logged-in.php';

		if ( ! empty( $customer['email'] ) && ! empty( $customer['first_name'] ) && ! has_action( 'edd_purchase_form_user_info_fields' ) ) {
			return;
		}
		$customer_info_complete = false;
	}
	?>
	<div class="edd-blocks__checkout-user">
		<?php
		$forms = get_personal_info_forms( $block_attributes, $customer_info_complete );
		$count = count( $forms );
		if ( ! empty( $forms ) && $count > 1 ) {
			wp_enqueue_script( 'edd-blocks-checkout-forms' );
			$i     = 0;
			$class = 'edd-blocks__checkout-forms';
			if ( $count < 3 ) {
				$class .= ' edd-blocks__checkout-forms--inline';
			}
			echo '<div class="' . esc_attr( $class ) . '">';
			foreach ( $forms as $id => $form ) {
				printf(
					'<button class="edd-button-secondary edd-blocks__checkout-%1$s link" data-attr="%1$s"%2$s>%3$s</button>',
					esc_attr( $id ),
					empty( $i ) ? ' disabled' : '',
					esc_html( $form['label'] )
				);
				++$i;
			}
			echo '</div>';
		}
		$form = reset( $forms );
		echo '<div class="edd-checkout-block__personal-info">';
		if ( is_callable( $form['view'] ) ) {
			echo call_user_func( $form['view'], array( 'current' => true ) );
		} else {
			include $form['view'];
		}
		?>
		</div>
	</div>
	<?php
}

/**
 * Outputs the purchase form for checkout.
 *
 * @since 2.0
 * @param array $block_attributes The block attributes.
 * @return void
 */
function do_purchase_form( $block_attributes ) {
	$payment_mode = edd_get_chosen_gateway();
	$form_action  = edd_get_checkout_uri( 'payment-mode=' . $payment_mode );
	do_action( 'edd_before_purchase_form' );
	?>
	<form id="edd_purchase_form" class="edd_form edd-blocks-form edd-blocks-form__purchase" action="<?php echo esc_url( $form_action ); ?>" method="POST">
		<?php
		do_personal_info_forms( $block_attributes );
		if ( edd_show_gateways() && edd_get_cart_total() > 0 ) {
			include EDD_BLOCKS_DIR . 'views/checkout/purchase-form/gateways.php';
		}
		if ( ! edd_show_gateways() ) {
			do_action( 'edd_purchase_form' );
		} else {
			?>
			<div id="edd_purchase_form_wrap"></div>
			<?php
		}
		?>
	</form>
	<?php
}

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\add_user_script' );
/**
 * Adds the guest checkout button switcher script to the EDD global checkout script.
 *
 * @since 2.0
 * @return void
 */
function add_user_script() {
	if ( ! edd_is_checkout() ) {
		return;
	}

	// Manually trigger a blur event on the hidden email input for logged in users.
	if ( is_user_logged_in() ) {
		$script = "jQuery(document).ready(function($) {
			$( '#edd-email[type=\"hidden\"]' ).trigger( 'blur' );
		} );";

		wp_add_inline_script( 'edd-checkout-global', $script );
		return;
	}

	wp_register_script( 'edd-blocks-checkout-forms', EDD_BLOCKS_URL . 'assets/js/checkout-forms.js', array( 'edd-checkout-global' ), EDD_VERSION, true );
}

add_action( 'wp_ajax_nopriv_edd_blocks_swap_personal_info', __NAMESPACE__ . '\swap_form' );
add_action( 'wp_ajax_edd_blocks_swap_personal_info', __NAMESPACE__ . '\swap_form' );
/**
 * Swaps out the currently displayed form for the selected one.
 *
 * @since 2.0
 * @return void
 */
function swap_form() {
	if ( empty( $_GET['form_id'] ) ) {
		return;
	}
	$form_id = sanitize_text_field( $_GET['form_id'] );
	$forms   = get_forms();
	if ( empty( $forms[ $form_id ] ) ) {
		return;
	}
	$form     = $forms[ $form_id ];
	$customer = \EDD\Blocks\Checkout\get_customer();
	if ( is_callable( $form['view'] ) ) {
		wp_send_json_success( call_user_func( $form['view'], array( 'current' => true ) ) );
	}

	ob_start();
	$form = include $form['view'];
	wp_send_json_success( ob_get_clean() );
}

/**
 * Gets the array of forms for the checkout form.
 *
 * @since 3.2.4
 * @return array
 */
function get_forms() {
	return array(
		'login'    => array(
			'label' => __( 'Log in', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/login.php',
		),
		'register' => array(
			'label' => __( 'Register for a new account', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/register.php',
		),
		'guest'    => array(
			'label' => __( 'Check out as a guest', 'easy-digital-downloads' ),
			'view'  => EDD_BLOCKS_DIR . 'views/checkout/purchase-form/personal-info.php',
		),
	);
}
