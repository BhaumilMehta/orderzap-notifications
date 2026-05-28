<?php
/**
 * Global helper functions for OrderZap Notifications.
 *
 * @package WcWan
 */

defined( 'ABSPATH' ) || exit;

function wc_wan_settings(): \WcWan\Core\Settings {
    static $instance = null;
    if ( null === $instance ) {
        $instance = new \WcWan\Core\Settings();
    }
    return $instance;
}

function wc_wan_is_enabled(): bool {
    return (bool) wc_wan_settings()->get( 'enabled' );
}

function wc_wan_get_order( int $order_id ): ?\WC_Order {
    $order = wc_get_order( $order_id );
    return ( $order instanceof \WC_Order ) ? $order : null;
}

function wc_wan_format_phone( string $phone ): string {
    $phone = preg_replace( '/[^\d+]/', '', trim( $phone ) );
    if ( empty( $phone ) )        { return ''; }
    if ( str_starts_with( $phone, '+' ) ) { return $phone; }
    if ( strlen( $phone ) === 10 )        { return '+91' . $phone; }
    if ( strlen( $phone ) > 10 )          { return '+' . $phone; }
    return '';
}

function wc_wan_log( $message ): void {
    if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) { return; }
    if ( is_array( $message ) || is_object( $message ) ) {
        $message = wp_json_encode( $message );
    }
    error_log( '[WC_WAN] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}
