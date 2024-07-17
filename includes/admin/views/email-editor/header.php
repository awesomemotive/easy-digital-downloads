<?php
/**
 * Email Editor: Header
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

$message      = filter_input( INPUT_GET, 'edd-message', FILTER_SANITIZE_SPECIAL_CHARS );
$status_badge = false;
if ( $message ) {
	$badges = array(
		'email-saved'     => array(
			'label' => __( 'Email Updated', 'easy-digital-downloads' ),
		),
		'email-not-saved' => array(
			'label'  => __( 'Email Not Updated', 'easy-digital-downloads' ),
			'status' => 'error',
			'icon'   => 'no',
		),
	);

	if ( isset( $badges[ $message ] ) ) {
		$args = wp_parse_args(
			$badges[ $message ],
			array(
				'status'   => 'success',
				'class'    => 'edd-email-status-badge',
				'position' => 'before',
				'icon'     => 'yes-alt',
			)
		);

		$status_badge = new \EDD\Utils\StatusBadge( $args );
	}
}
?>

<div class="edd-editor__header">
	<div class="edd-editor__header--actions">
		<div class="edd-editor__title">
			<h2><?php echo esc_html( $email->get_name() ); ?></h2>
			<?php require_once 'status.php'; ?>
		</div>
		<div class="edd-editor__actions">
			<?php
			require_once 'actions.php';
			submit_button( __( 'Save', 'easy-digital-downloads' ), 'primary', 'submit', false );
			?>
		</div>
		<?php
		$loading = new \EDD\Utils\StatusBadge(
			array(
				'label'    => __( 'Saving Changes', 'easy-digital-downloads' ),
				'status'   => 'info',
				'icon'     => 'info',
				'class'    => array( 'edd-email-status-badge', 'edd-hidden' ),
				'position' => 'before',
			)
		);
		echo $loading->get();
		if ( $status_badge ) {
			echo $status_badge->get();
		}
		?>
	</div>
</div>
<hr class="wp-header-end">
