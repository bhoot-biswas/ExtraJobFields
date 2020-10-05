<?php
/**
 * Extra_Job_Fields setup
 *
 * @package Extra_Job_Fields
 */

namespace BengalStudio\Extra_Job_Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Main Extra_Job_Fields Class.
 */
final class Extra_Job_Fields {

	/**
	 * The single instance of the class.
	 *
	 * @var Extra_Job_Fields
	 */
	protected static $_instance = null; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Main Extra_Job_Fields Instance.
	 * Ensures only one instance of Extra_Job_Fields is loaded or can be loaded.
	 *
	 * @return Extra_Job_Fields - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Extra_Job_Fields Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init();
	}

	/**
	 * Define Extra_Job_Fields Constants.
	 */
	private function define_constants() {
		define( 'EXTRA_JOB_FIELDS_VERSION', '0.1.0' );
		define( 'EXTRA_JOB_FIELDS_ABSPATH', dirname( EXTRA_JOB_FIELDS_PLUGIN_FILE ) . '/' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 * e.g. include_once EXTRA_JOB_FIELDS_ABSPATH . 'includes/foo.php';
	 */
	private function includes() {
		include_once EXTRA_JOB_FIELDS_ABSPATH . 'includes/util.php';
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private  function init() {
		add_filter( 'submit_job_form_fields', array( $this, 'frontend_add_job_fields' ) );
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'admin_add_job_fields' ) );
		add_action( 'single_job_listing_meta_end', array( $this, 'display_job_fields_data' ) );
	}

	/**
	 * Add frontend job fields.
	 * @param [type] $fields [description]
	 */
	public function frontend_add_job_fields( $fields ) {
		$fields['job']['number_of_vacancies'] = array(
			'label'       => __( 'Number of vacancies', 'extra-job-fields' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'e.g. 2',
			'priority'    => 7,
		);
		return $fields;
	}

	/**
	 * Add admin job fields.
	 * @param [type] $fields [description]
	 */
	public function admin_add_job_fields( $fields ) {
		$fields['_number_of_vacancies'] = array(
			'label'       => __( 'Number of vacancies', 'extra-job-fields' ),
			'type'        => 'text',
			'placeholder' => 'e.g. 2',
			'description' => '',
		);
		return $fields;
	}

	/**
	 * Display job fields data.
	 * @return [type] [description]
	 */
	public function display_job_fields_data() {
		global $post;

		$vacancies = get_post_meta( $post->ID, '_number_of_vacancies', true );

		if ( $vacancies ) {
			echo '<li>' . sprintf( __( 'Number of vacancies: %s', 'extra-job-fields' ), esc_html( $vacancies ) ) . '</li>';
		}
	}

	/**
	 * Get the URL for the Extra_Job_Fields plugin directory.
	 *
	 * @return string URL
	 */
	public static function plugin_url() {
		return untrailingslashit( plugins_url( '/', EXTRA_JOB_FIELDS_PLUGIN_FILE ) );
	}
}

Extra_Job_Fields::instance();
