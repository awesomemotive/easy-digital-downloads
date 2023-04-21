<?php
/**
 * Helper class to add a custom class to a downlaod menu item.
 *
 * @since 3.1.1
 */
namespace EDD\Admin\Menu;

class LinkClass {

	/**
	 * Adds a custom CSS class to a download menu item based on matching a target.
	 *
	 * @since 3.1.1
	 * @param string $target
	 * @param string $class
	 */
	public function __construct( $target, $class ) {
		global $submenu;

		if ( empty( $submenu['edit.php?post_type=download'] ) ) {
			return;
		}

		$target_position = $this->get_target_position( $submenu, $target );
		if ( is_null( $target_position ) ) {
			return;
		}

		// Prepare an HTML class.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $submenu['edit.php?post_type=download'][ $target_position ][4] ) ) {
			$submenu['edit.php?post_type=download'][ $target_position ][4] .= " {$class}";
		} else {
			$submenu['edit.php?post_type=download'][ $target_position ][] = $class;
		}
	}

	/**
	 * Gets the target position/key in the submenu.
	 *
	 * @since 3.1.1
	 * @param array $submenu
	 * @param string $target
	 * @return null|int
	 */
	private function get_target_position( $submenu, $target ) {
		return key(
			array_filter(
				$submenu['edit.php?post_type=download'],
				static function( $item ) use ( $target ) {

					if ( $target === $item[2] ) {
						return true;
					};

					return false !== strpos( $item[2], $target );
				}
			)
		);
	}
}
