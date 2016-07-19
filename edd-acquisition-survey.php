<?php
/**
 * Plugin Name:     Easy Digital Downloads - Acquisition Survey
 * Plugin URI:      https://easydigitaldownloads.com/extensions/acquisition-survey
 * Description:     Get feedback and statistics about where your customers are hearing about your site
 * Version:         1.0.2
 * Author:          Chris Klosowski
 * Author URI:      https://easydigitaldownloads.com
 * Text Domain:     edd-acquisition-survey
 *
 * @package         EDD\AcquisitionSurvey
 * @author          Chris Klosowski
 * @copyright       Copyright (c) 2015
 *
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Acquisition_Survey' ) ) {

	/**
	 * Main EDD_Acquisition_Survey class
	 *
	 * @since       1.0
	 */
	class EDD_Acquisition_Survey {

		/**
		 * @var         EDD_Acquisition_Survey $instance The one true EDD_Acquisition_Survey
		 * @since       1.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0
		 * @return      object self::$instance The one true EDD_Acquisition_Survey
		 */
		public static function instance() {
			if( !self::$instance ) {
				self::$instance = new EDD_Acquisition_Survey();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			if ( ! defined( 'EDD_ACQUISITION_SURVEY_VER' ) ) {
				define( 'EDD_ACQUISITION_SURVEY_VER', '1.0.2' );
			}

			// Plugin path
			if ( ! defined( 'EDD_ACQUISITION_SURVEY_DIR' ) ) {
				define( 'EDD_ACQUISITION_SURVEY_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL
			if ( ! defined( 'EDD_ACQUISITION_SURVEY_URL' ) ) {
				define( 'EDD_ACQUISITION_SURVEY_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'EDD_ACQUISITION_SURVEY_FILE' ) ) {
				define( 'EDD_ACQUISITION_SURVEY_FILE', __FILE__ );
			}
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function includes() {

			require_once EDD_ACQUISITION_SURVEY_DIR . 'includes/scripts.php';
			require_once EDD_ACQUISITION_SURVEY_DIR . 'includes/functions.php';

			if ( is_admin() ) {
				require_once EDD_ACQUISITION_SURVEY_DIR . 'includes/admin/settings.php';
				require_once EDD_ACQUISITION_SURVEY_DIR . 'includes/admin/hooks.php';
			}

		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function hooks() {

			// Register settings
			add_filter( 'edd_settings_extensions', 'edd_acq_settings', 1 );

			// Handle licensing
			// @todo        Replace the Acquisition Survey and Your Name with your data
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Acquisition Survey', EDD_ACQUISITION_SURVEY_VER, 'Chris Klosowski' );
			}

		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDD_ACQUISITION_SURVEY_DIR . '/languages/';
			$lang_dir = apply_filters( 'edd_Acquisition_Survey_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-acquisition-survey' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-acquisition-survey', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/edd-acquisition-survey/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-acquisition-survey/ folder
				load_textdomain( 'edd-acquisition-survey', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-acquisition-survey/languages/ folder
				load_textdomain( 'edd-acquisition-survey', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-acquisition-survey', false, $lang_dir );
			}
		}

	}

} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Acquisition_Survey
 * instance to functions everywhere
 *
 * @since       1.0
 * @return      \EDD_Acquisition_Survey The one true EDD_Acquisition_Survey
 *
 * @todo        Inclusion of the activation code below isn't mandatory, but
 *              can prevent any number of errors, including fatal errors, in
 *              situations where your extension is activated but EDD is not
 *              present.
 */
function edd_acquisition_survey_load() {
	if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if( ! class_exists( 'EDD_Extension_Activation' ) ) {
			require_once 'includes/class.extension-activation.php';
		}

		$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();
		return EDD_Acquisition_Survey::instance();
	} else {
		return EDD_Acquisition_Survey::instance();
	}
}
add_action( 'plugins_loaded', 'edd_acquisition_survey_load' );


/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0
 * @return      void
 */
function edd_acquisition_survey_activation() {
	$current_methods = get_option( 'edd_acq_methods', array() );

	if ( ! empty( $current_methods ) ) {
		return;
	}

	$methods = array();
	$methods[] = array( 'name' => 'Google'       , 'value' => 'google' );
	$methods[] = array( 'name' => 'Twitter'      , 'value' => 'twitter' );
	$methods[] = array( 'name' => 'Facebook Page', 'value' => 'facebook-page' );
	$methods[] = array( 'name' => 'Facebook Ads' , 'value' => 'facebook-ads' );
	$methods[] = array( 'name' => 'Friend'       , 'value' => 'friends' );
	$methods[] = array( 'name' => 'Online Ads'   , 'value' => 'online-ads' );
	$methods[] = array( 'name' => 'Other'        , 'value' => 'other' );

	add_option( 'edd_acq_methods', $methods, '', 'no' );
}
register_activation_hook( __FILE__, 'edd_acquisition_survey_activation' );
