<?php
/**
 * Discount editor form.
 *
 * @package     EDD\Admin\Discounts\Editor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Discounts\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Forms\Handler;

/**
 * Discount editor form.
 *
 * @since 3.3.9
 */
class Form {

	/**
	 * The discount object.
	 *
	 * @var \EDD_Discount
	 */
	private $discount;

	/**
	 * Constructor.
	 *
	 * @param \EDD_Discount $discount The discount object.
	 */
	public function __construct( $discount ) {
		$this->discount = $discount;
	}

	/**
	 * Render the form.
	 */
	public function render() {
		if ( ! $this->discount ) {
			$this->discount                    = new \EDD_Discount();
			$this->discount->id                = null;
			$this->discount->name              = '';
			$this->discount->status            = 'active';
			$this->discount->code              = '';
			$this->discount->amount            = '';
			$this->discount->amount_type       = 'percent';
			$this->discount->notes             = array();
			$this->discount->scope             = 'global';
			$this->discount->max_uses          = '';
			$this->discount->min_charge_amount = '';
		}
		?>
		<form method="POST">
			<?php Header::render( $this->discount ); ?>
			<div class="wrap">
				<?php do_action( 'edd_edit_discount_form_top', $this->discount->id, $this->discount ); ?>
				<div class="edd-form edd-form__discount">
					<?php
					$fields = array(
						Name::class,
						Status::class,
						Code::class,
						Amount::class,
						Products::class,
						Excluded::class,
						Categories::class,
						Start::class,
						Expiration::class,
						Minimum::class,
						MaxUses::class,
						UseOnce::class,
						Notes::class,
						Hidden::class,
					);
					Handler::render_fields( $fields, $this->discount );
					?>
					<?php do_action( 'edd_edit_discount_form_bottom', $this->discount->id, $this->discount ); ?>
				</div>
			</div>
		</form>
		<?php
	}
}
