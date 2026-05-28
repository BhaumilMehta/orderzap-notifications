<?php
/**
 * Plugin Name:       OrderZap Notifications
 * Description:       Automatically send WhatsApp notifications to customers based on WooCommerce order events. Supports Meta WhatsApp Cloud API & Twilio.
 * Version:           1.0.0
 * Author:            Bhaumil Mehta
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       orderzap-notifications
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * WC requires at least: 7.0
 * WC tested up to:   8.5
 *
 * @package WcWan
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'WC_WAN_VERSION',  '1.0.0' );
define( 'WC_WAN_FILE',     __FILE__ );
define( 'WC_WAN_PATH',     plugin_dir_path( __FILE__ ) );
define( 'WC_WAN_URL',      plugin_dir_url( __FILE__ ) );
define( 'WC_WAN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC_WAN_DB_VERSION', '1.0.0' );

/**
 * Autoloader.
 *
 * Namespace:  WcWan\Core\Plugin
 * Maps to:    inc/core/Plugin.php
 *
 * Sub-namespace segments are lowercased to match folder names;
 * the class filename keeps its original casing.
 */
spl_autoload_register( function ( $class ) {
    $prefix   = 'WcWan\\';
    $base_dir = WC_WAN_PATH . 'inc/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );

    $parts      = explode( '\\', $relative_class );
    $class_name = array_pop( $parts );
    $dir_parts  = array_map( 'strtolower', $parts );

    $file = $base_dir
        . ( $dir_parts ? implode( '/', $dir_parts ) . '/' : '' )
        . $class_name . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

/**
 * Check if WooCommerce is active.
 */
function wc_wan_check_woocommerce(): bool {
    return in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) ),
        true
    );
}

/**
 * Admin notice when WooCommerce is missing.
 */
function wc_wan_woocommerce_missing_notice(): void {
    echo '<div class="notice notice-error"><p>' .
        esc_html__( 'OrderZap Notifications requires WooCommerce to be installed and active.', 'orderzap-notifications' ) .
        '</p></div>';
}

/**
 * Boot the plugin.
 */
function wc_wan_init(): void {
    if ( ! wc_wan_check_woocommerce() ) {
        add_action( 'admin_notices', 'wc_wan_woocommerce_missing_notice' );
        return;
    }

    \WcWan\Core\Plugin::instance();
}
add_action( 'plugins_loaded', 'wc_wan_init' );

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, function () {
    require_once WC_WAN_PATH . 'inc/database/Installer.php';
    \WcWan\Database\Installer::run();
    flush_rewrite_rules();
} );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
} );
