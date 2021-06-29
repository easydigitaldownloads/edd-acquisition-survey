<?php
/**
 * Admin Hooks
 *
 * @package     EDD\AcquisitionSurvey\Admin\Hooks
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Adds the 'Acquisition' item to the reports dropdown
 *
 * @since  1.0
 * @param  array $reports The array of existing report types
 * @return array          The reports with Acquisition added
 */
function edd_acq_add_reports_item( $reports ) {
	$reports['acquisition'] = __( 'Acquisition', 'edd-acquisition-survey' );

	return $reports;
}

/**
 * Renders the Acquisition Table
 *
 * @since 1.0
 * @return void
 */
function edd_reports_acquisition_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-acquisition-reports-table.php' );

	$downloads_table = new EDD_Acquisition_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}

/**
 * Registers the Acquisition report.
 *
 * @since 1.0.3
 *
 * @param \EDD\Reports\Data\Report_Registry $reports Report registry.
 * @return void
 */
function edd_acq_register_acquisition_report( $reports ) {
	try {
		$reports->add_report( 'acquisition', array(
			'label'     => __( 'Acquisition', 'edd-acquisition-survey' ),
			'icon'      => 'chart-area',
			'priority'  => 45,
			'endpoints' => array(
				'tables' => array( 'acquisition_report' ),
			),
			'filters'   => array(),
		) );

		$reports->register_endpoint( 'acquisition_report', array(
			'label' => __( 'Acquisition Methods', 'edd-acquisition-survey' ),
			'views' => array(
				'table' => array(
					'display_args' => array(
						'class_name' => 'EDD_Acquisition_Reports_Table',
						'class_file' => EDD_ACQUISITION_SURVEY_DIR . 'includes/admin/class-acquisition-reports-table.php',
					),
				),
			),
		) );
	} catch ( \EDD_Exception $exception ) {
		edd_debug_log_exception( $exception );
	}
}

if ( function_exists( 'edd_get_orders' ) ) {
	add_action( 'edd_reports_init', 'edd_acq_register_acquisition_report' );
} else {
	add_filter( 'edd_report_views', 'edd_acq_add_reports_item', 10, 1 );
	add_action( 'edd_reports_view_acquisition', 'edd_reports_acquisition_table' );
}


/**
 * Shows the acquisition method on the payment details in admin
 *
 * @since  1.0
 * @param  int $payment_id The Payment ID
 * @return void
 */
function edd_acq_method_payment_details( $payment_id ) {
	$acquisition_method = edd_get_payment_meta( $payment_id, '_edd_payment_acquisition_method', true );
	if ( ! empty( $acquisition_method ) ) :
		$current_methods = edd_acq_get_methods();
		foreach ( $current_methods as $method ) {
			if ( $method['value'] === $acquisition_method ) {
				$acquisition_name = $method['name'];
				break;
			}
		}

		if ( empty( $acquisition_name ) ) {
			$acquisition_name = $acquisition_method . ' - ' . __( 'inactive' , 'edd-acquisition-survey' );
		}
		?>
		<div class="edd-order-acquisition-method edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Acquisition:', 'edd-acquisition-survey' ); ?></span>&nbsp;
				<span><?php echo $acquisition_name; ?></span>
			</p>
		</div>
		<?php
	endif;
}
add_action( 'edd_view_order_details_payment_meta_after', 'edd_acq_method_payment_details', 10, 1 );
