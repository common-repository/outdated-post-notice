<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   FSOutdatedPostNotice
 * @author    Firdaus Zahari <fake@fsylum.net>
 * @license   GPL-2.0+
 * @link      http://fsylum.net
 * @copyright 2014 Firdaus Zahari
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}