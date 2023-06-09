<?php
/**
 * EDW Admin Settings Class.
 *
 * Adapted from code in woocommerce 2.3
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Edwiser Bridge
 * @subpackage Edwiser Bridge/admin
 */

namespace app\wisdmlabs\edwiserBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Eb_Admin_Settings' ) ) {

	/**
	 * Eb_Admin_Settings.
	 */
	class Eb_Admin_Settings {
		/**
		 * Settings.
		 *
		 * @var array $settings settings.
		 */
		private static $settings = array();

		/**
		 * Errors.
		 *
		 * @var array $errors errors.
		 */
		private static $errors = array();

		/**
		 * Messages.
		 *
		 * @var array $messages messages.
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 */
		public static function get_settings_pages() {
			if ( empty( self::$settings ) ) {
				$settings = array();

				// include the settings page class.
				include_once 'settings/class-eb-settings-page.php';

				$settings[]     = include 'settings/class-eb-settings-general.php';
				$settings[]     = include 'settings/class-eb-settings-connection.php';
				$settings[]     = include 'settings/class-eb-settings-synchronization.php';
				$settings[]     = include 'settings/class-eb-settings-paypal.php';
				$settings[]     = include 'settings/class-eb-settings-pro-featuers.php';
				$settings[]     = include 'settings/class-eb-settings-dummy.php';
				self::$settings = apply_filters( 'eb_get_settings_pages', $settings );
				$settings[]     = include 'licensing/class-licensing-settings.php';
				$settings[]     = include 'settings/class-eb-bridge-summary.php';
				$settings[]     = include 'settings/class-eb-settings-shortcode-doc.php';
				$settings[]     = include 'settings/class-eb-error-log.php';
				$settings[]     = include 'settings/class-eb-settings-other-plugins.php';
			}

			return self::$settings;
		}

		/**
		 * Save the settings.
		 *
		 * @since  1.0.0
		 */
		public static function save() {
			global $current_tab;

			$referer = '';
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'eb-settings' ) ) {
				die( esc_html__( 'Action failed. Please refresh the page and retry.', 'edwiser-bridge' ) );
			}

			$postdata = $_POST;
			if ( isset( $postdata['_wp_http_referer'] ) ) {
				$referer = sanitize_text_field( wp_unslash( $postdata['_wp_http_referer'] ) );
			}

			// Trigger actions.
			do_action( 'eb_settings_save_' . $current_tab );
			do_action( 'eb_update_options_' . $current_tab );
			do_action( 'eb_update_options' );
			if ( ! in_array( $current_tab, array( 'licensing', 'logs' ), true ) ) {
				self::add_message( __( 'Your settings have been saved.', 'edwiser-bridge' ) );
			}
			do_action( 'eb_settings_saved' );
		}

		/**
		 * Add a message.
		 *
		 * @since  1.0.0
		 *
		 * @param string $text message text.
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error.
		 *
		 * @since  1.0.0
		 *
		 * @param string $text Error message.
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Output messages + errors.
		 *
		 * @since  1.0.0
		 */
		public static function show_messages() {
			if ( count( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
				}
			} elseif ( count( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					echo '<div id="message" class="updated fade">
							<p>
								<strong>' . esc_html( $message ) . '</strong>
							</p>
						</div>';
				}
			}
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main edw settings page in admin.
		 *
		 * @since  1.0.0
		 */
		public static function output() {
			global $current_section, $current_tab;

			do_action( 'eb_settings_start' );

			// Include settings pages.
			self::get_settings_pages();

			// Get current tab/section.
			$current_tab     = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general';
			$current_section = isset( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : '';

			// Save data only if nonce is verified.
			if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'eb-settings' ) && ! empty( $_POST ) ) {
				// Save settings if data has been posted.
				self::save();
			}

			// Show deault data if nonce is not verified.
			// Add any posted messages.
			if ( isset( $_GET['wp_error'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['wp_error'] ) ) ) ) {
				self::add_error( sanitize_text_field( wp_unslash( $_GET['wp_error'] ) ) );
			}

			if ( isset( $_GET['wp_message'] ) && ! empty( $_GET['wp_message'] ) ) {
				self::add_message( sanitize_text_field( wp_unslash( $_GET['wp_message'] ) ) );
			}

			self::show_messages();

			// Get tabs for the settings page.
			$tabs = apply_filters( 'eb_settings_tabs_array', array() );

			$tabname = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general';

			require_once plugin_dir_path( __DIR__ ) . 'admin/partials/html-admin-settings.php';
		}

		/**
		 * Get a setting from the settings API.
		 *
		 * @since  1.0.0
		 *
		 * @param string $option_name field name for which value to be fetched.
		 * @param string $current_tab tab in which the above field resides.
		 * @param string $default     default value to be returned in case field value not found.
		 *
		 * @return option value
		 */
		public static function get_option( $option_name, $current_tab, $default = '' ) {

			// get options of current tab.
			$options_values = get_option( 'eb_' . $current_tab );

			// Get value.
			$option_value = null;
			if ( isset( $options_values[ $option_name ] ) ) {
				$option_value = $options_values[ $option_name ];
			}

			if ( is_array( $option_value ) ) {
				$option_value = array_map( 'stripslashes', $option_value );
			} elseif ( ! is_null( $option_value ) ) {
				$option_value = stripslashes( $option_value );
			}
			return null === $option_value ? $default : $option_value;
		}


		/**
		 * DEPRECATED FUNCTION
		 *
		 * Output admin fields.
		 *
		 * @deprecated since 2.0.1 use output_fields( $options ) insted.
		 * Loops though the edw options array and outputs each field.
		 *
		 * @since  1.0.0
		 *
		 * @param array $options Opens array to output.
		 */
		public static function outputFields( $options ) {
			self::output_fields( $options );
		}



		/**
		 * Output admin fields.
		 *
		 * Loops though the edw options array and outputs each field.
		 *
		 * @since  1.0.0
		 *
		 * @param array $options Opens array to output.
		 */
		public static function output_fields( $options ) {
			global $current_tab;

			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}
				if ( ! isset( $value['id'] ) ) {
					$value['id'] = '';
				}
				if ( ! isset( $value['title'] ) ) {
					$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
				}
				if ( ! isset( $value['class'] ) ) {
					$value['class'] = '';
				}
				if ( ! isset( $value['css'] ) ) {
					$value['css'] = '';
				}
				if ( ! isset( $value['default'] ) ) {
					$value['default'] = '';
				}
				if ( ! isset( $value['desc'] ) ) {
					$value['desc'] = '';
				}
				if ( ! isset( $value['desc_tip'] ) ) {
					$value['desc_tip'] = false;
				}
				if ( ! isset( $value['placeholder'] ) ) {
					$value['placeholder'] = '';
				}

				// Custom attribute handling.
				$custom_attributes = array();
				if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
					foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling.
				$field_description = self::get_field_description( $value );
				$description       = $field_description['description'];
				$tooltip_html      = $field_description['tooltip_html'];

				// Switch based on type.
				switch ( $value['type'] ) {
					// Section Titles.
					case 'title':
						if ( ! empty( $value['title'] ) ) {
							?>
							<h3 class="<?php echo esc_attr( $value['class'] ); ?>">
								<?php echo esc_html( $value['title'] ); ?>
							</h3>
							<?php
						}
						if ( ! empty( $value['desc'] ) ) {
							echo wp_kses_post( $value['desc'] );
						}
						echo '<table class="form-table">' . "\n\n";
						if ( ! empty( $value['id'] ) ) {
							do_action( 'eb_settings_' . sanitize_title( $value['id'] ) );
						}
						break;

					// Section Ends.
					case 'sectionend':
						if ( false === empty( $value['id'] ) ) {
							do_action( 'eb_settings_' . sanitize_title( $value['id'] ) . '_end' );
						}
						echo '</table>';
						if ( ! empty( $value['id'] ) ) {
							do_action( 'eb_settings_' . sanitize_title( $value['id'] ) . '_after' );
						}
						break;

					// Standard text inputs and subtypes like 'number'.
					case 'text':
					case 'email':
					case 'url':
					case 'number':
					case 'color':
					case 'password':
						$type         = $value['type'];
						$option_value = self::get_option( $value['id'], $current_tab, $value['default'] );

						if ( 'color' === $value['type'] ) {
							$type            = 'text';
							$value['class'] .= 'colorpick';
							$description    .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '"
							class="colorpickdiv" style="z-index: 100;background:#eee;
							border:1px solid #ccc;position:absolute;display:none;"></div>';
						}
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>">
									<?php echo esc_html( $value['title'] ); ?>
								</label>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="<?php echo esc_attr( $type ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
									/>
									<?php echo wp_kses_post( $description ); ?>
							</td>
						</tr>
						<?php
						break;

					// Textarea.
					case 'textarea':
						$option_value = self::get_option( $value['id'], $current_tab, $value['default'] );
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>">
									<?php echo esc_html( $value['title'] ); ?>
								</label>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp forminp-<?php echo esc_html( sanitize_title( $value['type'] ) ); ?>">
								<?php echo wp_kses_post( $description ); ?>

								<textarea
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>><?php echo esc_textarea( $option_value ); ?></textarea>
							</td>
						</tr>
						<?php
						break;

					// Button input.
					case 'button':
						$type         = $value['type'];
						$option_value = $value['default'];
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
							</th>
							<td class="forminp forminp-<?php echo esc_html( sanitize_title( $value['type'] ) ); ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="<?php echo esc_attr( $type ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?> />
									<?php echo wp_kses_post( $description ); ?>
							</td>
						</tr>
						<?php
						break;

					// Select boxes.
					case 'select':
					case 'multiselect':
						$option_value = self::get_option( $value['id'], $current_tab, $value['default'] );

						$option_name = ( 'multiselect' === $value['type'] ) ? esc_attr( $value['id'] ) . '[]' : esc_attr( $value['id'] );
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>">
									<?php echo esc_html( $value['title'] ); ?>
								</label>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp forminp-<?php echo esc_html( sanitize_title( $value['type'] ) ); ?>">
								<select
								name="<?php echo esc_html( $option_name ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									<?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
									<?php echo ( 'multiselect' === $value['type'] ) ? 'multiple="multiple"' : ''; ?>>
										<?php
										if ( isset( $value['default'] ) && ! empty( $value['default'] ) ) {
											?>
											<option value=""> <?php echo esc_attr( $value['default'] ); ?></option>
											<?php
										}

										foreach ( $value['options'] as $key => $val ) {
											?>
										<option value="<?php echo esc_attr( $key ); ?>"
												<?php
												if ( is_array( $option_value ) ) {
													selected( in_array( trim( $key ), $option_value, true ), true );
												} else {
													selected( $option_value, $key );
												}
												?>
												><?php echo esc_html( $val ); ?></option> 
											<?php
										}
										?>
								</select>
								<?php echo wp_kses_post( $description ); ?>
							</td>
						</tr>
						<?php
						break;

					// Radio inputs.
					case 'radio':
						$option_value = self::get_option( $value['id'], $current_tab, $value['default'] );
						?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>">
									<?php echo esc_html( $value['title'] ); ?>
								</label>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<fieldset>
									<?php echo wp_kses_post( $description ); ?>
									<ul>
										<?php
										foreach ( $value['options'] as $key => $val ) {
											?>
											<li>
												<label>
													<input
														name="<?php echo esc_attr( $value['id'] ); ?>"
														value="<?php echo esc_html( $key ); ?>"
														type="radio"
														style="<?php echo esc_attr( $value['css'] ); ?>"
														class="<?php echo esc_attr( $value['class'] ); ?>"
														<?php echo esc_html( implode( ' ', $custom_attributes ) ); ?>
														<?php checked( $key, $option_value ); ?> /> 
														<?php echo esc_html( $val ); ?>
												</label>
											</li>
											<?php
										}
										?>
									</ul>
								</fieldset>
							</td>
						</tr>
						<?php
						break;

					// Checkbox input.
					case 'checkbox':
						$option_value    = self::get_option( $value['id'], $current_tab, $value['default'] );
						$visbility_class = array();

						if ( ! isset( $value['hide_if_checked'] ) ) {
							$value['hide_if_checked'] = false;
						}
						if ( ! isset( $value['show_if_checked'] ) ) {
							$value['show_if_checked'] = false;
						}
						if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
							$visbility_class[] = 'wdm_hidden_option';
						}
						if ( 'option' === $value['hide_if_checked'] ) {
							$visbility_class[] = 'hide_options_if_checked';
						}
						if ( 'option' === $value['show_if_checked'] ) {
							$visbility_class[] = 'show_options_if_checked';
						}

						if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
							?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
								<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?>
								</th>
								<td class="forminp forminp-checkbox">
									<fieldset>
									<?php } else { ?>
										<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
										<?php } if ( ! empty( $value['title'] ) ) { ?>
											<legend class="screen-reader-text">
												<span>
													<?php echo esc_html( $value['title'] ); ?>
												</span>
											</legend>
										<?php } ?>
										<label for="<?php echo esc_attr( $value['id'] ); ?>">
											<input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												id="<?php echo esc_attr( $value['id'] ); ?>"
												type="checkbox"
												value="1"
												<?php checked( $option_value, 'yes' ); ?>
												<?php echo esc_html( implode( ' ', $custom_attributes ) ); ?> />
												<?php echo wp_kses_post( $description ); ?>
										</label>
										<?php echo wp_kses_post( $tooltip_html ); ?>
										<?php
										if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
											?>
										</fieldset>
								</td>
							</tr>
											<?php
										} else {
											?>
							</fieldset>
											<?php
										}
						break;

					// Single page selects.
					case 'single_select_page':
						$args = array(
							'name'             => $value['id'],
							'id'               => $value['id'],
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => $value['class'],
							'echo'             => false,
							'selected'         => absint( self::get_option( $value['id'], $current_tab ) ),
						);

						if ( isset( $value['args'] ) ) {
							$args = wp_parse_args( $value['args'], $args );
						}
						?>
						<tr valign="top" class="single_select_page">
							<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp">
								<?php
								echo wp_kses( str_replace( ' id=', " data-placeholder='" . __( 'Select a page', 'edwiser-bridge' ) . "'style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ), \app\wisdmlabs\edwiserBridge\wdm_eb_get_allowed_html_tags() );
								echo wp_kses_post( $description );
								?>
							</td>
						</tr>
						<?php
						break;

					// Single sidebar select.
					case 'select_sidebar':
						$args = array(
							'name'             => $value['id'],
							'id'               => $value['id'],
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => $value['class'],
							'echo'             => false,
							'selected'         => self::get_option( $value['id'], $current_tab ),
						);

						if ( isset( $value['args'] ) ) {
							$args = wp_parse_args( $value['args'], $args );
						}
						?>
						<tr valign="top" class="single_select_page">
							<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?>
								<?php echo wp_kses_post( $tooltip_html ); ?>
							</th>
							<td class="forminp">
								<select name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>">
									<option selected><?php esc_html_e( '- Select a sidebar -', 'edwiser-bridge' ); ?></option>
									<?php
									$sidebars = $GLOBALS['wp_registered_sidebars'];
									foreach ( $sidebars as $sidebar ) {
										?>
										<option value="<?php echo esc_attr( $sidebar['id'] ); ?>" <?php selected( $args['selected'], $sidebar['id'] ); ?>>
											<?php echo esc_attr( $sidebar['name'] ); ?>
										</option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<?php
						break;
					case 'horizontal_line':
						?>
						<tr valign="top" class="single_select_page">
							<td>
								<hr>
							</td>
						</tr>
						<?php
						break;
					case 'cust_html':
						?>
							<tr>
								<!-- <td> -->
									<?php
									echo wp_kses_post( $value['html'] );
									?>
								<!-- </td> -->
							</tr>
							<?php
						break;

					default:
						do_action( 'eb_admin_field_' . $value['type'], $current_tab, $value );
						break;
				}
			}
		}

		/**
		 * DEPRECATED FUNCTION
		 *
		 * Save admin fields.
		 *
		 * Loops though the edw options array and outputs each field.
		 *
		 * @deprecated since 2.0.1 use save_fields( $options ) insted.
		 * @since  1.0.0
		 *
		 * @param array $options Opens array to output.
		 *
		 * @return bool
		 */
		public static function saveFields( $options ) {
			return self::save_fields( $options );
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the edw options array and outputs each field.
		 *
		 * @since  1.0.0
		 *
		 * @param array $options Opens array to output.
		 *
		 * @return bool
		 */
		public static function save_fields( $options ) {
			global $current_tab;
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'eb-settings' ) ) {
				die( esc_html__( 'Action failed. Please refresh the page and retry.', 'edwiser-bridge' ) );
			}

			$postdata = $_POST;

			if ( empty( $postdata ) ) {
				return false;
			}

			// Options to update will be stored here.
			$update_options = array();

			// Loop options and get values to save.
			foreach ( $options as $value ) {

				if ( ! isset( $value['id'] ) || ! isset( $value['type'] ) ) {
					continue;
				}

				// Get posted value.
				if ( strstr( $value['id'], '[' ) ) {
					parse_str( $value['id'], $option_name_array );

					$option_name  = current( array_keys( $option_name_array ) );
					$setting_name = key( $option_name_array[ $option_name ] );
					$option_value = null;

					if ( isset( $postdata[ $option_name ][ $setting_name ] ) ) {
						$option_value = \app\wisdmlabs\edwiserBridge\wdm_eb_edwiser_sanitize_array( ( wp_unslash( $postdata[ $option_name ][ $setting_name ] ) ) );
					}
				} else {
					$option_name  = $value['id'];
					$setting_name = '';
					$option_value = null;

					if ( isset( $postdata[ $value['id'] ] ) ) {
						$option_value = \app\wisdmlabs\edwiserBridge\wdm_eb_edwiser_sanitize_array( wp_unslash( $postdata[ $value['id'] ] ) );
					}
				}

				// Format value.
				switch ( sanitize_title( $value['type'] ) ) {
					case 'checkbox':
						if ( is_null( $option_value ) ) {
							$option_value = 'no';
						} else {
							$option_value = 'yes';
						}
						break;
					case 'textarea':
						$option_value = wp_kses_post( trim( $option_value ) );
						break;
					case 'text':
					case 'email':
					case 'url':
					case 'number':
					case 'select':
					case 'color':
					case 'password':
					case 'single_select_page':
					case 'radio':
						$option_value = wdm_edwiser_bridge_wp_clean( $option_value );
						break;
					case 'multiselect':
						$option_value = array_filter( array_map( 'wpClean', (array) $option_value ) );
						break;
					default:
						do_action( 'eb_update_option_' . sanitize_title( $value['type'] ), $value );
						break;
				}

				if ( ! is_null( $option_value ) ) {
					// Check if option is an array.
					if ( $option_name && $setting_name ) {
						// Get old option value.
						if ( ! isset( $update_options[ $option_name ] ) ) {
							$update_options[ $option_name ] = get_option( $option_name, array() );
						}

						if ( ! is_array( $update_options[ $option_name ] ) ) {
							$update_options[ $option_name ] = array();
						}

						$update_options[ $option_name ][ $setting_name ] = $option_value;

						// Single value.
					} else {
						$update_options[ $option_name ] = $option_value;
					}
				}

				// Custom handling.
				do_action( 'eb_update_option', $value );
			}

			// Now save the options.
			$upd_opt_filtered = array_filter( $update_options );
			update_option( 'eb_' . $current_tab, $upd_opt_filtered );

			return true;
		}

		/**
		 * Helper function to get the formated description and tip HTML for a
		 * given form field. Plugins can call this when implementing their own custom
		 * settings types.
		 *
		 * @since  1.0.0
		 *
		 * @param array $value The form field value array.
		 * @returns array The description and tip as a 2 element array.
		 */
		public static function get_field_description( $value ) {
			$description   = '';
			$tooltip_html  = '';
			$eb_plugin_url = \app\wisdmlabs\edwiserBridge\wdm_edwiser_bridge_plugin_url();

			if ( true === $value['desc_tip'] ) {
				$tooltip_html = $value['desc'];
			} elseif ( ! empty( $value['desc_tip'] ) ) {
				$description  = $value['desc'];
				$tooltip_html = $value['desc_tip'];
			} elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
			}

			if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
				$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
			} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
				$description = wp_kses_post( $description );
			} elseif ( in_array( $value['type'], array( 'button' ), true ) ) {
				$description = '<span class="load-response">
									<img src="' . $eb_plugin_url . 'images/loader.gif" height="20" width="20" />
								</span>
								<span class="linkresponse-box"></span>
								<span class="response-box"></span>
								<div id="unlinkerrorid-modal" class="unlinkerror-modal">
								  <div class="unlinkerror-modal-content">
									<span class="unlinkerror-modal-close">&times;</span>
									<table class="unlink-table">
									 <thead>
										<tr>
										   <th>' . esc_html__( 'User ID', 'edwiser-bridge' ) . '</th>
										   <th>' . esc_html__( 'Name', 'edwiser-bridge' ) . '</th>
										</tr>
									 </thead>
									 <tbody>
									 </tbody>
								  </table>
								  </div>
								 </div>';
			} elseif ( $description ) {
				$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
			}

			if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
				$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
			} elseif ( $tooltip_html && in_array( $value['type'], array( 'button' ), true ) ) {
				$tooltip_html = '';
			} elseif ( $tooltip_html ) {
				$tooltip_html = '<img class="help_tip"
									data-tip="' . esc_attr( $tooltip_html ) . '"
									src="' . $eb_plugin_url . 'images/help.png"
									height="20"
									width="20" />';
			}

			return array(
				'description'  => $description,
				'tooltip_html' => $tooltip_html,
			);
		}
	}
}
new Eb_Admin_Settings();

/**
 * Deprecated Class.
 *
 * @deprecated 3.0.0
 */
Class EbAdminSettings extends Eb_Admin_Settings { // @codingStandardsIgnoreLine

}
