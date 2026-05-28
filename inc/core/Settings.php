<?php
/**
 * Settings – manages plugin options.
 *
 * @package WcWan\Core
 */

namespace WcWan\Core;

defined( 'ABSPATH' ) || exit;

class Settings {

    private const OPTION_KEY = 'wc_wan_settings';

    private array $defaults = [
        'enabled'              => false,
        'provider'             => 'meta',
        // Meta WhatsApp Cloud API
        'meta_access_token'    => '',
        'meta_phone_number_id' => '',
        'meta_waba_id'         => '',
        // Twilio
        'twilio_account_sid'   => '',
        'twilio_auth_token'    => '',
        'twilio_from_number'   => '',
        // Notification toggles
        'notify_pending'       => true,
        'notify_processing'    => true,
        'notify_completed'     => true,
        'notify_cancelled'     => true,
        'notify_refunded'      => true,
        'notify_failed'        => false,
        'notify_on_hold'       => false,
        'notify_customer_note' => true,
        'notify_tracking'      => true,
        // Misc
        'log_retention_days'   => 30,
        'test_phone'           => '',
    ];

    private array $settings;

    public function __construct() {
        $saved          = get_option( self::OPTION_KEY, [] );
        $this->settings = wp_parse_args( $saved, $this->defaults );
    }

    public function get( string $key, mixed $fallback = null ): mixed {
        return $this->settings[ $key ] ?? $fallback;
    }

    public function all(): array {
        return $this->settings;
    }

    public function save( array $data ): bool {
        $sanitized      = $this->sanitize( $data );
        $this->settings = wp_parse_args( $sanitized, $this->defaults );
        return update_option( self::OPTION_KEY, $this->settings );
    }

    private function sanitize( array $data ): array {
        $clean = [];

        $bool_keys = [
            'enabled', 'notify_pending', 'notify_processing', 'notify_completed',
            'notify_cancelled', 'notify_refunded', 'notify_failed', 'notify_on_hold',
            'notify_customer_note', 'notify_tracking',
        ];
        $string_keys = [
            'provider', 'meta_access_token', 'meta_phone_number_id', 'meta_waba_id',
            'twilio_account_sid', 'twilio_auth_token', 'twilio_from_number', 'test_phone',
        ];

        foreach ( $bool_keys as $key ) {
            if ( isset( $data[ $key ] ) ) {
                $clean[ $key ] = (bool) $data[ $key ];
            }
        }
        foreach ( $string_keys as $key ) {
            if ( isset( $data[ $key ] ) ) {
                $clean[ $key ] = sanitize_text_field( $data[ $key ] );
            }
        }
        if ( isset( $data['log_retention_days'] ) ) {
            $clean['log_retention_days'] = absint( $data['log_retention_days'] );
        }

        return $clean;
    }

    public function defaults(): array {
        return $this->defaults;
    }
}
