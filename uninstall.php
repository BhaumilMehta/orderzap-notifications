<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WcWan
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$options = [
    'wc_wan_settings',
    'wc_wan_db_version',
    'wc_wan_templates',
];
foreach ( $options as $option ) {
    delete_option( $option );
}

$tables = [
    $wpdb->prefix . 'wc_wan_logs',
    $wpdb->prefix . 'wc_wan_templates',
];
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}
