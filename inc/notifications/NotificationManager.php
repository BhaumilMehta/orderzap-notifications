<?php
/**
 * NotificationManager – wires WooCommerce hooks to WhatsApp dispatch.
 *
 * @package WcWan\Notifications
 */

namespace WcWan\Notifications;

defined( 'ABSPATH' ) || exit;

use WcWan\Core\Settings;
use WcWan\Core\TemplateParser;
use WcWan\Core\WhatsAppManager;
use WcWan\Logs\Logger;

class NotificationManager {

    private Settings $settings;
    private TemplateParser $parser;
    private WhatsAppManager $manager;
    private Logger $logger;

    public function __construct( Logger $logger ) {
        $this->settings = new Settings();
        $this->parser   = new TemplateParser();
        $this->manager  = new WhatsAppManager( $this->settings );
        $this->logger   = $logger;
    }

    public function on_order_status_changed( int $order_id, string $old_status, string $new_status, \WC_Order $order ): void {
        if ( ! $this->settings->get( 'notify_' . $new_status, false ) ) {
            return;
        }
        $phone = $order->get_billing_phone();
        if ( empty( $phone ) ) { return; }

        $template = $this->get_template( $new_status );
        if ( empty( $template ) ) { return; }

        $message = $this->parser->parse( $template, $order );
        $result  = $this->manager->send( $phone, $message );

        $this->logger->log( [
            'order_id'   => $order_id,
            'event'      => 'status_' . $new_status,
            'recipient'  => $phone,
            'message'    => $message,
            'status'     => $result['success'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'],
            'error'      => $result['error'],
        ] );
    }

    public function on_customer_note( array $args ): void {
        if ( ! $this->settings->get( 'notify_customer_note' ) ) { return; }

        $order_id = absint( $args['order_id'] );
        $note     = sanitize_textarea_field( $args['customer_note'] );
        $order    = wc_get_order( $order_id );
        if ( ! $order instanceof \WC_Order ) { return; }

        $phone = $order->get_billing_phone();
        if ( empty( $phone ) ) { return; }

        $template = $this->get_template( 'customer_note' );
        if ( empty( $template ) ) { return; }

        $message = $this->parser->parse( $template, $order, [ 'note_message' => $note ] );
        $result  = $this->manager->send( $phone, $message );

        $this->logger->log( [
            'order_id'   => $order_id,
            'event'      => 'customer_note',
            'recipient'  => $phone,
            'message'    => $message,
            'status'     => $result['success'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'],
            'error'      => $result['error'],
        ] );
    }

    public function on_checkout_complete( int $order_id, array $posted_data ): void {
        do_action( 'wc_wan_checkout_complete', $order_id, $posted_data );
    }

    public function send_tracking_notification( int $order_id, string $tracking_number, string $courier_name, string $tracking_url ): array {
        if ( ! $this->settings->get( 'notify_tracking' ) ) {
            return [ 'success' => false, 'error' => 'Tracking notifications disabled.' ];
        }

        $order = wc_get_order( $order_id );
        if ( ! $order instanceof \WC_Order ) {
            return [ 'success' => false, 'error' => 'Order not found.' ];
        }

        $phone = $order->get_billing_phone();
        if ( empty( $phone ) ) {
            return [ 'success' => false, 'error' => 'No phone number on order.' ];
        }

        $order->update_meta_data( '_wc_wan_tracking_number', sanitize_text_field( $tracking_number ) );
        $order->update_meta_data( '_wc_wan_courier_name',    sanitize_text_field( $courier_name ) );
        $order->update_meta_data( '_wc_wan_tracking_url',    esc_url_raw( $tracking_url ) );
        $order->save();

        $template = $this->get_template( 'tracking' );
        if ( empty( $template ) ) {
            return [ 'success' => false, 'error' => 'Tracking template not configured.' ];
        }

        $message = $this->parser->parse( $template, $order, [
            'tracking_number' => $tracking_number,
            'courier_name'    => $courier_name,
            'tracking_url'    => $tracking_url,
        ] );
        $result = $this->manager->send( $phone, $message );

        $this->logger->log( [
            'order_id'   => $order_id,
            'event'      => 'tracking',
            'recipient'  => $phone,
            'message'    => $message,
            'status'     => $result['success'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'],
            'error'      => $result['error'],
        ] );

        return $result;
    }

    private function get_template( string $event ): string {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_wan_templates';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT template FROM `{$table}` WHERE event_slug = %s AND is_active = 1 LIMIT 1", $event ) ); // phpcs:ignore
        return $row ? $row->template : '';
    }
}
