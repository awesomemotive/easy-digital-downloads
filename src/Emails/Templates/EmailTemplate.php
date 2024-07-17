<?php

namespace EDD\Emails\Templates;
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore
use EDD\Emails\Email;
use EDD\Cron\Events\SingleEvent;

/**
 * Class EmailTemplate
 *
 * @since 3.3.0
 * @package EDD\Emails\Templates
 */
abstract class EmailTemplate implements TemplateInterface {
	use Traits\Legacy;
	use Traits\Actions;
	use Traits\Previews;

	/**
	 * Whether the email can be viewed in the admin.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	public $can_view = true;

	/**
	 * The email ID.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id;

	/**
	 * The email recipient.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $recipient;

	/**
	 * The email context.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $context = 'order';

	/**
	 * The email sender.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $sender = 'edd';

	/**
	 * Whether the email can be previewed.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_preview = false;

	/**
	 * Whether a test email can be sent.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	protected $can_test = false;

	/**
	 * Email "meta" data.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	protected $meta = array();

	/**
	 * The email preview data.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	protected $preview_data;

	/**
	 * Tag that **must** be present in the email.
	 *
	 * @since 3.3.0
	 * @var null|string
	 */
	protected $required_tag;

	/**
	 * The email object.
	 *
	 * @since 3.3.0
	 * @var EDD\Emails\Email
	 */
	protected $email;

	/**
	 * Whether the email has been installed to the database.
	 * This is nearly always true.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	private $installed = true;

	/**
	 * EmailTemplate constructor.
	 *
	 * @since 3.3.0
	 * @param string $email_id The email ID.
	 * @param Email  $email    Optional. The email object, if already instantiated.
	 */
	public function __construct( $email_id = '', $email = null ) {
		if ( $email instanceof Email ) {
			$this->email = $email;
		}
	}

	/**
	 * Get the default value for a property.
	 *
	 * @since 3.3.0
	 * @param string $property The property to get the default value for.
	 * @return mixed
	 */
	public function get_default( $property ) {
		$defaults = $this->defaults();

		return array_key_exists( $property, $defaults ) ? $defaults[ $property ] : '';
	}

	/**
	 * Magic getter.
	 *
	 * @param string $key The email property to retrieve.
	 * @return mixed|null
	 */
	public function __get( $key ) {

		if ( 'status' === $key ) {
			return (bool) $this->is_enabled();
		}

		if ( 'preview_data' === $key ) {
			return $this->set_preview_data();
		}

		$email = $this->get_email();
		if ( property_exists( $email, $key ) && ! is_null( $email->{$key} ) ) {
			return $email->{$key};
		}

		if ( is_callable( array( $this, "get_{$key}" ) ) ) {
			return $this->{"get_{$key}"}();
		}

		if ( property_exists( $this, $key ) && ! is_null( $this->{$key} ) ) {
			return $this->{$key};
		}

		$legacy = $this->get_legacy( $key );
		if ( $legacy ) {
			return $legacy;
		}

		return $this->get_default( $key );
	}

	/**
	 * Determines whether an email property can be edited.
	 *
	 * @since 3.3.0
	 * @param string $key The email property to check.
	 * @return bool
	 */
	public function can_edit( $key ): bool {
		if ( 'status' === $key && ! $this->are_base_requirements_met() ) {
			return false;
		}

		return in_array( $key, $this->get_editable_properties(), true );
	}

	/**
	 * Gets the email context as a label.
	 * This is an optional function to allow for more descriptive labels.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_context_label(): string {
		return $this->context;
	}

	/**
	 * Gets the content for the status tooltip, if needed.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_status_tooltip(): array {
		if ( $this->can_edit( 'status' ) ) {
			return array();
		}

		$content = __( 'This email cannot be disabled.', 'easy-digital-downloads' );
		if ( ! $this->is_enabled() ) {
			$content = __( 'This email cannot be enabled.', 'easy-digital-downloads' );
		}

		return array(
			'content'  => $content,
			'dashicon' => 'dashicons-lock',
		);
	}

	/**
	 * If a tag is required, this will add it to the email tags for the editor
	 * if it's not already present. A tag can be added, but not required, just by using the
	 * `get_required_tag_parameters` method. This must contain a label and description, and a tag
	 * if the required tag is not set.
	 *
	 * @return void
	 */
	final public function maybe_add_required_tag() {
		$tag = $this->get_required_tag_parameters();
		if ( empty( $tag ) ) {
			return;
		}
		if ( $this->required_tag && EDD()->email_tags->email_tag_exists( $this->required_tag ) ) {
			return;
		}
		$description = $tag['description'];
		if ( ! empty( $this->required_tag ) ) {
			$description .= ' ' . __( 'This tag is required for this email.', 'easy-digital-downloads' );
		}
		EDD()->email_tags->add(
			$tag['tag'] ?? $this->required_tag,
			$description,
			'__return_true',
			$tag['label'],
			array( $this->context )
		);
	}

	/**
	 * Gets the email metadata for a specific key.
	 *
	 * @since 3.3.0
	 * @param string $key The metadata key.
	 * @return mixed
	 */
	public function get_metadata( $key ) {
		$email = $this->get_email();
		if ( $email->id && metadata_exists( 'edd_email', $email->id, $key ) ) {
			return edd_get_email_meta( $email->id, $key, true );
		}

		$meta = $this->meta;
		if ( isset( $meta[ $key ] ) ) {
			return $meta[ $key ];
		}

		return $this->get_default( $key );
	}

	/**
	 * The email properties that can be edited.
	 *
	 * @return array
	 */
	protected function get_editable_properties(): array {
		return array(
			'content',
			'subject',
			'heading',
			'status',
		);
	}

	/**
	 * Determines whether the email is enabled.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	protected function is_enabled(): bool {
		if ( ! $this->are_base_requirements_met() ) {
			return false;
		}

		$email = $this->get_email();

		return (bool) $email->status;
	}

	/**
	 * Determines whether the email's base requirements are met.
	 * Most emails will not need this.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	public function are_base_requirements_met(): bool {
		return true;
	}

	/**
	 * Gets the email object.
	 *
	 * @since 3.3.0
	 * @return EDD\Emails\Email
	 */
	public function get_email() {
		if ( ! $this->email ) {
			$this->email = $this->get_email_from_db();
		}

		return $this->email;
	}

	/**
	 * Adds the email to the database.
	 *
	 * @since 3.3.0
	 * @return false|int
	 */
	public function install() {
		if ( ! $this->can_view ) {
			return false;
		}

		if ( $this->installed && $this->get_email()->id ) {
			return false;
		}

		$email_id = edd_add_email( $this->get_email_data_for_installer() );
		if ( empty( $email_id ) ) {
			return false;
		}
		$this->install_metadata( $email_id );

		if ( $this->has_legacy_data() ) {
			SingleEvent::add(
				time() + 30 * DAY_IN_SECONDS,
				'edd_email_legacy_data_cleanup',
				array( $email_id )
			);
		}

		$this->installed = true;

		return $email_id;
	}

	/**
	 * Gets the required tag parameters for the email editor.
	 * Most emails will not need this. For those that do, just
	 * return an array with a label and description.
	 *
	 * @since 3.3.0
	 * @return array|false
	 */
	protected function get_required_tag_parameters() {
		return false;
	}

	/**
	 * Gets the email from the database.
	 * If the email does not exist, it will be installed.
	 *
	 * @since 3.3.0
	 * @return EDD\Emails\Email
	 */
	private function get_email_from_db() {
		$email = false;
		if ( $this->installed ) {
			$email = edd_get_email( $this->email_id );
		}
		if ( $email ) {
			return $email;
		}

		// If the email is not installed, install it.
		$this->installed = false;
		$email_id        = $this->install();
		if ( $email_id ) {
			return edd_get_email_by( 'id', $email_id );
		}

		// If the email could not be installed, create a new email object.
		return new Email( $this->email_id );
	}

	/**
	 * Gets the email data for the installer.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private function get_email_data_for_installer() {
		return array(
			'email_id'  => $this->email_id,
			'subject'   => $this->get_legacy( 'subject' ),
			'heading'   => $this->get_legacy( 'heading' ),
			'content'   => $this->get_legacy( 'content' ),
			'status'    => $this->get_legacy( 'status' ),
			'context'   => $this->context,
			'sender'    => $this->sender,
			'recipient' => $this->recipient,
		);
	}


	/**
	 * Installs metadata for the specified email template.
	 *
	 * @param int $email_id The ID of the email template.
	 * @return void
	 */
	private function install_metadata( $email_id ) {

		// Install legacy options meta.
		foreach ( $this->get_options() as $option ) {
			edd_add_email_meta( $email_id, 'legacy', $option );
		}

		$data = array();
		if ( empty( $this->meta ) ) {
			return;
		}

		foreach ( $this->meta as $key => $value ) {
			if ( ! empty( $value ) ) {
				$data[ $key ] = $value;
				continue;
			}

			if ( is_callable( array( $this, "get_{$key}" ) ) ) {
				$value = $this->{"get_{$key}"}();
				if ( ! empty( $value ) ) {
					$data[ $key ] = $value;
					continue;
				}
			}

			$legacy_value = $this->get_legacy( $key );
			if ( $legacy_value ) {
				$data[ $key ] = $legacy_value;
				continue;
			}

			$default_value = $this->get_default( $key );
			if ( $default_value ) {
				$data[ $key ] = $default_value;
			}
		}

		if ( empty( $data ) ) {
			return;
		}

		foreach ( $data as $key => $value ) {
			edd_update_email_meta( $email_id, $key, $value );
		}
	}
}
