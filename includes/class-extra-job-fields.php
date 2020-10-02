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
		add_filter( 'submit_job_form_fields', array( $this, 'frontend_add_salary_field' ) );
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'admin_add_salary_field' ) );
		add_action( 'single_job_listing_meta_end', array( $this, 'display_job_salary_data' ) );
		add_filter( 'wpjm_get_job_listing_structured_data', array( $this, 'add_basesalary_data' ) );
		add_action( 'job_manager_job_filters_search_jobs_end', array( $this, 'filter_by_salary_field' ) );
		add_filter( 'job_manager_get_listings', array( $this, 'filter_by_salary_field_query_args' ), 10, 2 );
		add_filter( 'manage_edit-job_listing_columns', array( $this, 'retrieve_salary_column' ) );
		add_filter( 'manage_job_listing_posts_custom_column', array( $this, 'display_salary_column' ) );
	}

	/**
	 * Add frontend salary field.
	 * @param [type] $fields [description]
	 */
	public function frontend_add_salary_field( $fields ) {
		$fields['job']['job_salary'] = array(
			'label'       => __( 'Salary ($)', 'job-salary' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'e.g. 20000',
			'priority'    => 7,
		);
		return $fields;
	}

	/**
	 * Add admin salary field.
	 * @param [type] $fields [description]
	 */
	public function admin_add_salary_field( $fields ) {
		$fields['_job_salary'] = array(
			'label'       => __( 'Salary ($)', 'job-salary' ),
			'type'        => 'text',
			'placeholder' => 'e.g. 20000',
			'description' => '',
		);
		return $fields;
	}

	/**
	 * Display job salary data.
	 * @return [type] [description]
	 */
	public function display_job_salary_data() {
		global $post;

		$salary = get_post_meta( $post->ID, '_job_salary', true );

		if ( $salary ) {
			echo '<li>' . __( 'Salary:' ) . ' $' . esc_html( $salary ) . '</li>';
		}
	}

	/**
	 * Add Google structured data.
	 * @param [type] $data [description]
	 */
	public function add_basesalary_data( $data ) {
		global $post;

		$data['baseSalary']                      = array();
		$data['baseSalary']['@type']             = 'MonetaryAmount';
		$data['baseSalary']['currency']          = 'USD';
		$data['baseSalary']['value']             = array();
		$data['baseSalary']['value']['@type']    = 'QuantitativeValue';
		$data['baseSalary']['value']['value']    = get_post_meta( $post->ID, '_job_salary', true );
		$data['baseSalary']['value']['unitText'] = 'YEAR';

		return $data;
	}

	/**
	 * This can either be done with a filter (below) or the field can be added directly to the job-filters.php template file!
	 * @return [type] [description]
	 */
	public function filter_by_salary_field() {
		?>
		<div class="search_salary">
			<label for="search_salary"><?php _e( 'Salary', 'job-salary' ); ?></label>
			<select name="filter_by_salary" class="job-manager-filter">
				<option value=""><?php _e( 'Any Salary', 'job-salary' ); ?></option>
				<option value="upto20"><?php _e( 'Up to $20,000', 'job-salary' ); ?></option>
				<option value="20000-40000"><?php _e( '$20,000 to $40,000', 'job-salary' ); ?></option>
				<option value="40000-60000"><?php _e( '$40,000 to $60,000', 'job-salary' ); ?></option>
				<option value="over60"><?php _e( '$60,000+', 'job-salary' ); ?></option>
			</select>
		</div>
		<?php
	}

	/**
	 * This code gets your posted field and modifies the job search query
	 * @param  [type] $query_args [description]
	 * @param  [type] $args       [description]
	 * @return [type]             [description]
	 */
	public function filter_by_salary_field_query_args( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by salary
			if ( ! empty( $form_data['filter_by_salary'] ) ) {
				$selected_range = sanitize_text_field( $form_data['filter_by_salary'] );
				switch ( $selected_range ) {
					case 'upto20':
						$query_args['meta_query'][] = array(
							'key'     => '_job_salary',
							'value'   => '20000',
							'compare' => '<',
							'type'    => 'NUMERIC',
						);
						break;
					case 'over60':
						$query_args['meta_query'][] = array(
							'key'     => '_job_salary',
							'value'   => '60000',
							'compare' => '>=',
							'type'    => 'NUMERIC',
						);
						break;
					default:
						$query_args['meta_query'][] = array(
							'key'     => '_job_salary',
							'value'   => array_map( 'absint', explode( '-', $selected_range ) ),
							'compare' => 'BETWEEN',
							'type'    => 'NUMERIC',
						);
						break;
				}

				// This will show the 'reset' link
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}
		}
		return $query_args;
	}

	/**
	 * Sets the job_salary metadata as a new $column that can be used in the back-end.
	 * @param  [type] $columns [description]
	 * @return [type]          [description]
	 */
	public function retrieve_salary_column( $columns ) {
		$columns['job_salary'] = __( 'Salary', 'job-salary' );
		return $columns;
	}

	/**
	 * Display salary column in the back-end.
	 * @param  [type] $column [description]
	 * @return [type]         [description]
	 */
	public function display_salary_column( $column ) {
		global $post;

		switch ( $column ) {
			case 'job_salary':
				$salary = get_post_meta( $post->ID, '_job_salary', true );

				if ( ! empty( $salary ) ) {
					echo $salary;
				} else {
					echo '-';
				}
				break;
		}

		return $column;
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
