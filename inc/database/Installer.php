<?php
/**
 * Installer – creates / updates plugin database tables.
 *
 * @package WcWan\Database
 */

namespace WcWan\Database;

defined( 'ABSPATH' ) || exit;

class Installer {

    public static function run(): void {
        self::create_tables();
        self::seed_default_templates();
        update_option( 'wc_wan_db_version', WC_WAN_DB_VERSION );
    }

    private static function create_tables(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $logs = $wpdb->prefix . 'wc_wan_logs';
        dbDelta( "CREATE TABLE IF NOT EXISTS `{$logs}` (
            id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id    BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
            event       VARCHAR(50)  NOT NULL DEFAULT '',
            recipient   VARCHAR(30)  NOT NULL DEFAULT '',
            message     TEXT         NOT NULL,
            status      VARCHAR(10)  NOT NULL DEFAULT 'failed',
            message_id  VARCHAR(100) NOT NULL DEFAULT '',
            error       VARCHAR(500) NOT NULL DEFAULT '',
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY status   (status),
            KEY created_at (created_at)
        ) {$charset};" );

        $tpl = $wpdb->prefix . 'wc_wan_templates';
        dbDelta( "CREATE TABLE IF NOT EXISTS `{$tpl}` (
            id          INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
            event_slug  VARCHAR(50)  NOT NULL DEFAULT '',
            label       VARCHAR(100) NOT NULL DEFAULT '',
            template    TEXT         NOT NULL,
            is_active   TINYINT(1)   NOT NULL DEFAULT 1,
            updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY event_slug (event_slug)
        ) {$charset};" );
    }

    private static function seed_default_templates(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_wan_templates';

        if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ) > 0 ) { // phpcs:ignore
            return;
        }

        $defaults = [
            [ 'pending',       'New Order (Pending)',    "Hello {customer_name},\n\nWe've received your order #{order_id} and it's awaiting payment.\n\nOrder Total: {order_total}\n\nThank you for shopping with {site_name}! 🛍️" ],
            [ 'processing',    'Order Processing',       "Hello {customer_name},\n\nYour order #{order_id} is being processed! 🎉\n\nOrder Total: {order_total}\n\nWe'll notify you once it ships.\n\n{site_name}" ],
            [ 'completed',     'Order Completed',        "Hello {customer_name},\n\nYour order #{order_id} has been completed. ✅\n\nOrder Total: {order_total}\n\nThank you for choosing {site_name}! 😊" ],
            [ 'cancelled',     'Order Cancelled',        "Hello {customer_name},\n\nYour order #{order_id} has been cancelled. 😔\n\nFor queries, please contact us.\n\n{site_name}" ],
            [ 'refunded',      'Order Refunded',         "Hello {customer_name},\n\nYour refund for order #{order_id} has been processed. 💰\n\nAmount: {order_total}\n\nAllow 3–7 business days.\n\n{site_name}" ],
            [ 'failed',        'Order Failed',           "Hello {customer_name},\n\nPayment for order #{order_id} failed. ❌\n\nPlease retry: {order_url}\n\n{site_name}" ],
            [ 'on-hold',       'Order On Hold',          "Hello {customer_name},\n\nYour order #{order_id} is on hold. ⏳\n\nWe'll update you shortly.\n\n{site_name}" ],
            [ 'customer_note', 'Customer Note',          "Hello {customer_name},\n\nA message about your order #{order_id}:\n\n{note_message}\n\n{site_name}" ],
            [ 'tracking',      'Shipment Tracking',      "Hello {customer_name},\n\nYour order #{order_id} has been shipped! 🚚\n\nCourier: {courier_name}\nTracking #: {tracking_number}\nTrack here: {tracking_url}\n\n{site_name}" ],
        ];

        foreach ( $defaults as [ $slug, $label, $template ] ) {
            $wpdb->insert( $table, [ 'event_slug' => $slug, 'label' => $label, 'template' => $template, 'is_active' => 1 ], [ '%s', '%s', '%s', '%d' ] );
        }
    }
}
