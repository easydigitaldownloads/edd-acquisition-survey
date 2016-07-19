<?php
/**
 * Admin Settings Functions
 *
 * @package     EDD\AcquisitionSurvey\Admin\Settings
 * @since       1.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the subsection for EDD 2.5 settings
 *
 * @since  1.0.2
 * @param  array $sections The array of subsections
 * @return array           Array of subsections with Acquisition Survey added
 */
function edd_acq_settings_section( $sections ) {
	$sections['acquisition-survey'] = __( 'Acquisition Survey', 'edd-acquisition-survey' );

	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_acq_settings_section', 10, 1 );

/**
 * Setup the settings for the Extensions tab
 *
 * @since  1.0
 * @param  array $settings The existing settings
 * @return array           The settings with ours added
 */
function edd_acq_settings( $settings ) {

	$new_settings = array(
		array(
			'id'    => 'edd_Acquisition_Survey_settings',
			'name'  => __( 'Acquisition Survey Settings', 'edd-acquisition-survey' ),
			'desc'  => __( 'Configure Acquisition Survey Settings', 'edd-acquisition-survey' ),
			'type'  => 'header',
		),
		'acq_survey_title' => array(
			'id'   => 'acq_survey_title',
			'name' => __( 'Survey Title', 'edd-acquisition-survey' ),
			'desc' => __( 'The heading for the survey', 'edd-acquisition-survey' ),
			'type' => 'text',
			'std'  => __( 'How did you hear about us?', 'edd-acquisition-survey' )
		),
		'acq_require_response' => array(
			'id'   => 'acq_require_response',
			'name' => __( 'Require survey response', 'edd-acquisition-survey' ),
			'desc' => __( 'When checked, the user must complete this field to complete the purchase', 'edd-acquisition-survey' ),
			'type' => 'checkbox'
		),
		'acquisition_methods' => array(
			'id'   => 'acquision_methods',
			'name' => __( 'Acquisition Methods', 'edd-acquisition-survey' ),
			'desc' => __( 'Define the options presented to customers.', 'edd-acquisition-survey' ),
			'type' => 'acquisition_methods'
		),
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$new_settings = array( 'acquisition-survey' => $new_settings );
	}

	return array_merge( $settings, $new_settings );
}

/**
 * Acquisition Methods Callback
 *
 * Renders Acquisition Methods table
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_acquisition_methods_callback( $args ) {

	global $edd_options;
	$methods = edd_acq_get_methods();
	ob_start(); ?>
	<p><?php echo $args['desc']; ?></p>
	<table id="edd-acquisition-methods" class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th scope="col" class="edd-acq-drag"></th>
				<th scope="col" class="edd-acq-name"><?php _e( 'Method Name', 'edd-acquisition-survey' ); ?></th>
				<th scope="col" class="edd-acq-value"><?php _e( 'Unique ID', 'edd-acquisition-survey' ); ?></th>
				<th scope="col" class="edd-acq-remove"></th>
			</tr>
		</thead>
		<?php if( ! empty( $methods ) ) : ?>
			<?php foreach( $methods as $key => $method ) : ?>
			<tr class="edd-acq-method-row">
				<td>
					<span class="edd_draghandle"></span>
				</td>
				<td class="edd-acq-name">
					<?php echo EDD()->html->text( array(
						'name'  => 'edd_acq_methods[' . $key . '][name]',
						'value' => $method['name'],
						'placeholder' => __( 'Name visible to Customers', 'edd-acquisition-survey' )
					) ); ?>
				</td>
				<td class="edd-acq-value">
					<?php echo EDD()->html->text( array(
						'name' => 'edd_acq_methods[' . $key . '][value]',
						'value' => $method['value'],
						'placeholder' => __( 'name-identifier', 'edd-acquisition-survey' )
					) ); ?>
				</td>
				<td><span class="edd-acq-remove-method button-secondary"><?php _e( 'Remove', 'edd-acquisition-survey' ); ?></span></td>
			</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr class="edd-acq-method-row">
				<td>
					<span class="edd_draghandle"></span>
				</td>
				<td class="edd-acq-name">
					<?php echo EDD()->html->text( array(
						'name'        => 'edd_acq_methods[0][name]',
						'placeholder' => __( 'Name visible to Customers', 'edd-acquisition-survey' )
					) ); ?>
				</td>
				<td class="edd-acq-value">
					<?php echo EDD()->html->text( array(
						'name'        => 'edd_acq_methods[0][value]',
						'placeholder' => __( 'name-identifier', 'edd-acquisition-survey' )
					) ); ?>
				</td>
				<td><span class="edd-acq-remove-method button-secondary"><?php _e( 'Remove', 'edd-acquisition-survey' ); ?></span></td>
			</tr>
		<?php endif; ?>
	</table>
	<p>
		<span class="button-secondary" id="edd-acq-add-method"><?php _e( 'Add Method', 'edd' ); ?></span>
	</p>
	<?php
	echo ob_get_clean();
}

/**
 * Acquisition Methods Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the acquisition methods table
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_acq_save_methods( $input ) {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	// If our acquisition methods are in the POST, move along.
	if ( ! array_key_exists( 'edd_acq_methods', $_POST ) ) {
		return $input;
	}

	$new_methods = ! empty( $_POST['edd_acq_methods'] ) ? array_values( $_POST['edd_acq_methods'] ) : array();

	$saved_methods = array();
	foreach ( $new_methods as $key => $method ) {
		if ( empty( $method['name'] ) && empty( $method['value'] ) ) {
			unset( $new_methods[$key] );
		}

		if ( ! in_array( $method['value'], $saved_methods ) ) {
			$saved_methods[] = $method['value'];
		} else {
			return $input;
		}
	}

	update_option( 'edd_acq_methods', $new_methods );

	return $input;
}
add_filter( 'edd_settings_extensions_sanitize', 'edd_acq_save_methods', 10, 1 );
