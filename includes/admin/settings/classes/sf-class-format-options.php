<?php

/**
 * Format an options array into HTML
 *
 * @author Matt Gates <http://mgates.me>
 * @package WordPress
 */

if ( ! class_exists( 'SF_Format_Options' ) ) {

	class SF_Format_Options extends SF_Settings_API {

		/**
		 * Format an option array into HTML
		 *
		 *
		 * @access public
		 *
		 * @param array   $value Single option array.
		 * @return string HTML.
		 */
		public function settings_options_format( $setting )
		{
			if ( empty( $setting ) ) return false;

			$defaults = array(
				'name'        => '',
				'desc'        => '',
				'placeholder' => '',
				'class'       => '',
				'tip'         => '',
				'id'          => '',
				'css'         => '',
				'type'        => 'text',
				'std'         => '',
				'select2'     => false,
				'multiple'    => false,
				'options'     => array(),
				'restrict'    => array(),
			);

			/* Each to it's own variable for slim-ness' sakes. */
			extract( shortcode_atts( $defaults, $setting ) );

			$restrict_defaults = array(
				'min'  => 0,
				'max'  => '',
				'step' => 'any',
			);

			$restrict = shortcode_atts( $restrict_defaults, $restrict );

			$value   = $this->get_option( $id );
			$value   = $value !== false ? maybe_unserialize( $value ) : false;

			// Sanitize the value
			if ( is_array($value)) {
				foreach ($value as $key => $output) {
					$value[$key] = esc_attr($output);
				}
			} else { $value = esc_attr($value); }

			$title   = $name;
			$name    = $this->id . "_options[{$id}]";

			$grouped = !$title ? ' style="padding-top:0px;"' : '';
			$tip     = SF_Format_Options::get_formatted_tip( $tip );

			$description = $desc && !$grouped && $type != 'checkbox'
				? '<br /><small>' . $desc . '</small>'
				: '<label for="' . $id . '"> ' .$desc . '</label>';

			$header_types = apply_filters( $this->id . '_options_header_types', array( 'heading', 'title' ) );

			$description = ( ( in_array($type, $header_types) || $type == 'radio' ) && !empty( $desc ) )
				? '<p>' . $desc . '</p>'
				: $description;

			?>

			<?php if ( !in_array( $type, $header_types ) ) : ?>
			<!-- Header of the option. -->
			<tr valign="top">
				<th scope="row"<?php echo $grouped; ?>>

					<?php echo $tip; ?>

					<?php if ( !$grouped ) : ?>
						<label for="<?php echo $name; ?>" class="description"><?php echo $title; ?></label>
					<?php endif; ?>

				</th>
				<td <?php echo $grouped; ?> >
			<?php endif; ?>

		<?php foreach ($header_types as $header) :
				if ( $type != $header ) continue; ?>
					<tr>
						<th scope="col" colspan="2">
							<h3 class="title"><?php echo $title; ?></h3>
							<?php echo $description; ?>
						</th>
					</tr>
		<?php endforeach; ?>

	<?php switch ( $type ) :

		case 'text'   :
		case 'number' : ?>
			<input name="<?php echo $name; ?>"
				 id="<?php echo $id; ?>"
				 type="<?php echo $type; ?>"

				 <?php if ( $type == 'number' ): ?>
				 min="<?php echo $restrict['min']; ?>"
				 max="<?php echo $restrict['max']; ?>"
				 step="<?php echo $restrict['step']; ?>"
				 <?php endif; ?>

				 class="regular-text <?php echo $class; ?>"
				 style="<?php echo $css; ?>"
				 placeholder="<?php echo $placeholder; ?>"
				 value="<?php echo $value !== false ? $value : $std; ?>"
				/>
			<?php echo $description;
			break;

		case 'checkbox':

			$selected = ( $value !== false ) ? $value : $std;

			if ( $multiple ) :

				foreach ($options as $key => $desc) : ?>

					<input name="<?php echo $name; ?><?php echo $multiple ? '[]' : ''; ?>"
							 id="<?php echo $id . '_' . $key; ?>"
							 type="checkbox"
							 class="<?php echo $class; ?>"
							 style="<?php echo $css; ?>"
							 value="<?php echo $key; ?>"
							 <?php self::checked( $value, $key ); ?>
							 />
						<label for="<?php echo $id . '_' . $key; ?>">
							<?php echo $desc; ?>
						</label>
						<br/>
					<?php

				endforeach;

			else : ?>

			<input name="<?php echo $name; ?>"
					 id="<?php echo $id ?>"
					 type="checkbox"
					 class="<?php echo $class; ?>"
					 style="<?php echo $css; ?>"
					 <?php checked( $selected, 1 ); ?>
					 />
			<?php echo $description;
			endif;
			break;

		case 'radio':

			$selected = ( $value !== false ) ? $value : $std;

			foreach ( $options as $key => $val ) : ?>
						<label class="radio">
						<input type="radio"
							   name="<?php echo $name; ?>"
							   id="<?php echo $key; ?>"
							   value="<?php echo $key; ?>"
							   class="<?php echo $class; ?>"
								<?php checked( $selected, $key ); ?>
						/>
						<?php echo $val; ?>
						</label><br />
			<?php endforeach;
			echo $description;
			break;

		case 'single_select_page':

			$selected = ( $value !== false ) ? $value : $std;

			$args = array(
				'name'       => $name,
				'id'         => $id,
				'sort_order' => 'ASC',
				'echo'       => 0,
				'selected'   => $selected
			);
			echo wp_dropdown_pages( $args );
			echo $description;

			if ( $select2 ) : ?>
				<script type="text/javascript">jQuery(function() {jQuery("#<?php echo $id; ?>").select2({ width: '350px' });});</script>
			<?php endif;

			break;

		case 'select':

			$selected = ( $value !== false ) ? $value : $std; ?>

			<select id="<?php echo $id; ?>"
					  class="<?php echo $class; ?>"
					  style="<?php echo $css; ?>"
					  name="<?php echo $name; ?><?php echo $multiple ? '[]' : ''; ?>"
					  <?php echo $multiple ? 'multiple="multiple"' : ''; ?>
					  >

			<?php foreach ( $options as $key => $val ) : ?>
						<option value="<?php echo $key; ?>" <?php self::selected( $selected, $key ); ?>>
						<?php echo $val; ?>
						</option>
			<?php endforeach; ?>
			</select>

			<?php echo $description;

			if ( $select2 ) : ?>
				<script type="text/javascript">jQuery(function() {jQuery("#<?php echo $id; ?>").select2({ width: '350px' });});</script>
			<?php endif;

			break;

		case 'textarea': ?>
			<textarea name="<?php echo $name; ?>"
								id="<?php echo $id; ?>"
								class="large-text <?php echo $class; ?>"
								style="<?php if ( $css ) echo $css; else echo 'width:300px;'; ?>"
								placeholder="<?php echo $placeholder; ?>"
								rows="3"
					  ><?php echo ( $value !== false ) ? $value : $std; ?></textarea>
					<?php echo $description;
			break;

		endswitch;

			/* Footer of the option. */
			if ( !in_array( $type, $header_types ) ) echo '</td></tr>';

		}

		private function selected( $haystack, $current ) {

			if ( is_array( $haystack ) && in_array( $current, $haystack ) ) {
				$current = $haystack = 1;
			}

			selected($haystack, $current);
		}

		private function checked( $haystack, $current ) {

			if ( is_array( $haystack ) && !empty( $haystack[$current] ) ) {
				$current = $haystack = 1;
			}

			checked($haystack, $current);
		}


		/**
		 * Format a tooltip given a string
		 *
		 * @param string $tip
		 * @return string
		 */
		private function get_formatted_tip( $tip )
		{
			return $tip ? sprintf( '<a href="#" title="%s" class="sf-tips" tabindex="99"></a>', $tip ) : '';
		}


	}


}
