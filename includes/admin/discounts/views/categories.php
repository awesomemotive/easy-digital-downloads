<?php
/**
 * Renders the categories field for the discount add/edit screens.
 *
 * @var array  $categories     The categories to display.
 * @var string $term_condition The term condition to display.
 */

defined( 'ABSPATH' ) || exit;

?>
<tr>
	<th scope="row" valign="top">
		<label for="edd-categories"><?php esc_html_e( 'Categories', 'easy-digital-downloads' ); ?></label>
	</th>
	<td>
		<?php
		$dropdown = new EDD\HTML\CategorySelect(
			array(
				'name'             => 'categories[]',
				'id'               => 'edd-categories',
				'selected'         => $categories ?: array(), // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
				'multiple'         => true,
				'chosen'           => true,
				'show_option_all'  => false,
				'show_option_none' => false,
				'number'           => 30,
			)
		);
		echo $dropdown->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<div id="edd-discount-category-conditions" style="<?php echo esc_attr( $categories ? '' : 'display:none;' ); ?>">
			<p>
				<select id="edd-term-condition" name="term_condition">
					<option value=""<?php selected( '', $term_condition ); ?>><?php esc_html_e( 'Only discount products in these categories', 'easy-digital-downloads' ); ?></option>
					<option value="exclude"<?php selected( 'exclude', $term_condition ); ?>><?php esc_html_e( 'Do not discount products in these categories', 'easy-digital-downloads' ); ?></option>
				</select>
			</p>
		</div>
		<p class="description">
			<?php esc_html_e( 'Optionally include/exclude products from this discount by category. Leave blank for any.', 'easy-digital-downloads' ); ?>
		</p>
	</td>
</tr>
