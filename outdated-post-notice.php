<?php
/**
 * @package   FSOutdatedPostNotice
 * @author    Firdaus Zahari <fake@fsylum.net>
 * @license   GPL-2.0+
 * @link      http://fsylum.net
 * @copyright 2014 Firdaus Zahari
 *
 * Plugin Name:       Outdated Post Notice
 * Plugin URI:        http://fsylum.net
 * Description:       Display a notice to the readers when a post last updated exceeds certain days
 * Version:           1.0.0
 * Author:            Firdaus Zahari
 * Author URI:        http://fsylum.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class/class-fs-outdated-post-notice.php' );
register_activation_hook( __FILE__, array( 'FSOutdatedPostNotice', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FSOutdatedPostNotice', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'FSOutdatedPostNotice', 'get_instance' ) );
