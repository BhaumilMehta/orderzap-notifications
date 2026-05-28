<?php
/**
 * MetaProvider – Meta WhatsApp Cloud API.
 *
 * @package WcWan\Providers
 */

namespace WcWan\Providers;

defined( 'ABSPATH' ) || exit;

use WcWan\Core\Settings;

class MetaProvider implements ProviderInterface {

    private const API_VERSION = 'v19.0';
    private const API_BASE    = 'https://graph.facebook.com/';

    private Settings $settings;

    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    public function send( string $to, string $message ): array {
        if ( ! $this->is_configured() ) {
            return [ 'success' => false, 'message_id' => '', 'error' => 'Meta provider not configured.' ];
        }

        $phone_number_id = $this->settings->get( 'meta_phone_number_id' );
        $access_token    = $this->settings->get( 'meta_access_token' );
        $url             = self::API_BASE . self::API_VERSION . '/' . $phone_number_id . '/messages';

        $response = wp_remote_post( $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $to,
                'type'              => 'text',
                'text'              => [ 'preview_url' => false, 'body' => $message ],
            ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message_id' => '', 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code && isset( $data['messages'][0]['id'] ) ) {
            return [ 'success' => true, 'message_id' => $data['messages'][0]['id'], 'error' => '' ];
        }

        return [ 'success' => false, 'message_id' => '', 'error' => $data['error']['message'] ?? 'Unknown Meta API error.' ];
    }

    public function is_configured(): bool {
        return ! empty( $this->settings->get( 'meta_access_token' ) )
            && ! empty( $this->settings->get( 'meta_phone_number_id' ) );
    }

    public function get_name(): string {
        return 'Meta WhatsApp Cloud API';
    }
}
