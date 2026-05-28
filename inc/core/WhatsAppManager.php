<?php
/**
 * WhatsAppManager – provider factory and message dispatcher.
 *
 * @package WcWan\Core
 */

namespace WcWan\Core;

defined( 'ABSPATH' ) || exit;

use WcWan\Providers\ProviderInterface;
use WcWan\Providers\MetaProvider;
use WcWan\Providers\TwilioProvider;

class WhatsAppManager {

    private Settings $settings;
    private ?ProviderInterface $provider = null;

    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    public function send( string $to, string $message ): array {
        if ( ! $this->settings->get( 'enabled' ) ) {
            return $this->result( false, '', 'Plugin disabled.' );
        }

        $to = $this->normalize_phone( $to );
        if ( empty( $to ) ) {
            return $this->result( false, '', 'Invalid phone number.' );
        }

        try {
            return $this->get_provider()->send( $to, $message );
        } catch ( \Exception $e ) {
            return $this->result( false, '', $e->getMessage() );
        }
    }

    private function get_provider(): ProviderInterface {
        if ( $this->provider ) {
            return $this->provider;
        }

        $slug = $this->settings->get( 'provider', 'meta' );

        $providers = apply_filters( 'wc_wan_providers', [
            'meta'   => new MetaProvider( $this->settings ),
            'twilio' => new TwilioProvider( $this->settings ),
        ] );

        if ( ! isset( $providers[ $slug ] ) ) {
    throw new \RuntimeException(
        sprintf(
            'Unknown WhatsApp provider: %s',
            esc_html( $slug )
        )
    );
	}

        $this->provider = $providers[ $slug ];
        return $this->provider;
    }

    /**
     * Normalize to E.164. Assumes +91 for bare 10-digit Indian numbers.
     */
    private function normalize_phone( string $phone ): string {
        $phone = preg_replace( '/[^\d+]/', '', trim( $phone ) );
        if ( empty( $phone ) ) { return ''; }
        if ( str_starts_with( $phone, '+' ) ) { return $phone; }
        if ( strlen( $phone ) === 10 ) { return '+91' . $phone; }
        if ( strlen( $phone ) > 10 )  { return '+' . $phone; }
        return '';
    }

    public function result( bool $success, string $message_id, string $error = '' ): array {
        return compact( 'success', 'message_id', 'error' );
    }
}
