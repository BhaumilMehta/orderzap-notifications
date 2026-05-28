<?php
/**
 * APIController – REST API endpoints.
 *
 * @package WcWan\Api
 */

namespace WcWan\Api;

defined( 'ABSPATH' ) || exit;

use WcWan\Core\Settings;
use WcWan\Core\WhatsAppManager;
use WcWan\Logs\Logger;
use WcWan\Notifications\NotificationManager;

class APIController {

    private const NAMESPACE = 'wc-wan/v1';

    private Logger $logger;

    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    public function register_routes(): void {
        register_rest_route( self::NAMESPACE, '/settings', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ $this, 'get_settings' ],  'permission_callback' => [ $this, 'admin_permission' ] ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ $this, 'save_settings' ], 'permission_callback' => [ $this, 'admin_permission' ] ],
        ] );
        register_rest_route( self::NAMESPACE, '/templates', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ $this, 'get_templates' ],  'permission_callback' => [ $this, 'admin_permission' ] ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ $this, 'save_templates' ], 'permission_callback' => [ $this, 'admin_permission' ] ],
        ] );
        register_rest_route( self::NAMESPACE, '/logs', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_logs' ],
            'permission_callback' => [ $this, 'admin_permission' ],
            'args'                => [
                'page'     => [ 'default' => 1,    'sanitize_callback' => 'absint' ],
                'per_page' => [ 'default' => 20,   'sanitize_callback' => 'absint' ],
                'status'   => [ 'default' => '',   'sanitize_callback' => 'sanitize_key' ],
            ],
        ] );
        register_rest_route( self::NAMESPACE, '/stats',    [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ $this, 'get_stats' ],    'permission_callback' => [ $this, 'admin_permission' ] ] );
        register_rest_route( self::NAMESPACE, '/test',     [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_test' ],    'permission_callback' => [ $this, 'admin_permission' ] ] );
        register_rest_route( self::NAMESPACE, '/tracking', [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_tracking' ],'permission_callback' => [ $this, 'admin_permission' ] ] );
    }

    public function get_settings(): \WP_REST_Response {
        return rest_ensure_response( ( new Settings() )->all() );
    }

    public function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $settings = new Settings();
        return rest_ensure_response( [ 'success' => $settings->save( $request->get_json_params() ) ] );
    }

    public function get_templates(): \WP_REST_Response {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_wan_templates';
        $rows  = $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY id ASC", ARRAY_A ); // phpcs:ignore
        return rest_ensure_response( $rows ?: [] );
    }

    public function save_templates( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $table     = $wpdb->prefix . 'wc_wan_templates';
        $templates = $request->get_json_params();
        if ( ! is_array( $templates ) ) {
            return new \WP_Error( 'invalid_data', 'Expected array of templates.', [ 'status' => 400 ] );
        }
        foreach ( $templates as $tpl ) {
            if ( empty( $tpl['event_slug'] ) ) { continue; }
            $wpdb->update(
                $table,
                [ 'template' => wp_kses_post( $tpl['template'] ?? '' ), 'is_active' => (int) ( $tpl['is_active'] ?? 1 ) ],
                [ 'event_slug' => sanitize_key( $tpl['event_slug'] ) ],
                [ '%s', '%d' ], [ '%s' ]
            );
        }
        return rest_ensure_response( [ 'success' => true ] );
    }

    public function get_logs( \WP_REST_Request $request ): \WP_REST_Response {
        return rest_ensure_response( $this->logger->get_logs( [
            'page'     => $request->get_param( 'page' ),
            'per_page' => $request->get_param( 'per_page' ),
            'status'   => $request->get_param( 'status' ),
        ] ) );
    }

    public function get_stats(): \WP_REST_Response {
        return rest_ensure_response( $this->logger->get_stats() );
    }

    public function send_test( \WP_REST_Request $request ): \WP_REST_Response {
        $settings = new Settings();
        $phone    = sanitize_text_field( $request->get_param( 'phone' ) ?: $settings->get( 'test_phone' ) );
        if ( empty( $phone ) ) {
            return new \WP_Error( 'no_phone', 'Test phone number required.', [ 'status' => 400 ] );
        }
        $manager = new WhatsAppManager( $settings );
        $message = sprintf( "✅ Test message from %s\n\nOrderZap Notifications is working correctly!\n\nTime: %s", get_bloginfo( 'name' ), current_time( 'H:i:s d/m/Y' ) );
        $result  = $manager->send( $phone, $message );
        $this->logger->log( [ 'order_id' => 0, 'event' => 'test', 'recipient' => $phone, 'message' => $message, 'status' => $result['success'] ? 'sent' : 'failed', 'message_id' => $result['message_id'], 'error' => $result['error'] ] );
        return rest_ensure_response( $result );
    }

    public function send_tracking( \WP_REST_Request $request ): \WP_REST_Response {
        $order_id = absint( $request->get_param( 'order_id' ) );
        if ( ! $order_id ) {
            return new \WP_Error( 'invalid_order', 'Valid order_id required.', [ 'status' => 400 ] );
        }
        $nm = new NotificationManager( $this->logger );
        return rest_ensure_response( $nm->send_tracking_notification(
            $order_id,
            sanitize_text_field( $request->get_param( 'tracking_number' ) ),
            sanitize_text_field( $request->get_param( 'courier_name' ) ),
            esc_url_raw( $request->get_param( 'tracking_url' ) )
        ) );
    }

    public function admin_permission(): bool {
        return current_user_can( 'manage_woocommerce' );
    }
}
