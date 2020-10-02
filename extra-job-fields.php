<?php
/**
 * Plugin Name:     Extra Job Fields
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     extra-job-fields
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Extra_Job_Fields
 */

defined( 'ABSPATH' ) || exit;

// Define EXTRA_JOB_FIELDS_PLUGIN_FILE.
if ( ! defined( 'EXTRA_JOB_FIELDS_PLUGIN_FILE' ) ) {
	define( 'EXTRA_JOB_FIELDS_PLUGIN_FILE', __FILE__ );
}

// Include the main Extra_Job_Fields class.
if ( ! class_exists( 'Extra_Job_Fields' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-extra-job-fields.php';
}
