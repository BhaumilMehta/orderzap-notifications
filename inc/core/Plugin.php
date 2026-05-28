<?php
/**
 * Core Plugin class – singleton bootstrap.
 *
 * @package WcWan\Core
 */

namespace WcWan\Core;

defined( 'ABSPATH' ) || exit;

use WcWan\Admin\Admin;
use WcWan\Api\APIController;
use WcWan\Notifications\NotificationManager;
use WcWan\Logs\Logger;

final class Plugin {

    private static ?Plugin $instance = null;
    public Loader $loader;
    public Logger $logger;
    public NotificationManager $notifications;

    private function __construct() {
        $this->loader        = new Loader();
        $this->logger        = new Logger();
        $this->notifications = new NotificationManager( $this->logger );

        $this->define_admin_hooks();
        $this->define_api_hooks();
        $this->define_notification_hooks();
        $this->loader->run();
    }

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    

    private function define_admin_hooks(): void {
        $admin = new Admin();
        $this->loader->add_action( 'admin_menu', $admin, 'register_menu' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
        $this->loader->add_filter( 'plugin_action_links_' . WC_WAN_BASENAME, $admin, 'plugin_action_links' );
    }

    private function define_api_hooks(): void {
        $api = new APIController( $this->logger );
        $this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
    }

    private function define_notification_hooks(): void {
        $nm = $this->notifications;
        $this->loader->add_action( 'woocommerce_order_status_changed', $nm, 'on_order_status_changed', 10, 4 );
        $this->loader->add_action( 'woocommerce_new_customer_note', $nm, 'on_customer_note', 10, 1 );
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $nm, 'on_checkout_complete', 10, 2 );
    }

    private function __clone() {}

    public function __wakeup(): void {
        throw new \Exception( 'Cannot unserialize singleton.' );
    }
}
