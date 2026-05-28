<?php
/**
 * TwilioProvider – Twilio WhatsApp API.
 *
 * @package WcWan\Providers
 */

namespace WcWan\Providers;

defined( 'ABSPATH' ) || exit;

use WcWan\Core\Settings;

class TwilioProvider implements ProviderInterface {

    private const API_BASE = 'https://api.twilio.com/2010-04-01/Accounts/';

    private Settings $settings;

    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    public function send( string $to, string $message ): array {
        if ( ! $this->is_configured() ) {
            return [ 'success' => false, 'message_id' => '', 'error' => 'Twilio provider not configured.' ];
        }

        $account_sid = $this->settings->get( 'twilio_account_sid' );
        $auth_token  = $this->settings->get( 'twilio_auth_token' );
        $from        = 'whatsapp:' . $this->settings->get( 'twilio_from_number' );
        $url         = self::API_BASE . $account_sid . '/Messages.json';

        $response = wp_remote_post( $url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( $account_sid . ':' . $auth_token ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => [ 'From' => $from, 'To' => 'whatsapp:' . $to, 'Body' => $message ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message_id' => '', 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( in_array( $code, [ 200, 201 ], true ) && isset( $data['sid'] ) ) {
            return [ 'success' => true, 'message_id' => $data['sid'], 'error' => '' ];
        }

        return [ 'success' => false, 'message_id' => '', 'error' => $data['message'] ?? 'Unknown Twilio error.' ];
    }

    public function is_configured(): bool {
        return ! empty( $this->settings->get( 'twilio_account_sid' ) )
            && ! empty( $this->settings->get( 'twilio_auth_token' ) )
            && ! empty( $this->settings->get( 'twilio_from_number' ) );
    }

    public function get_name(): string {
        return 'Twilio WhatsApp API';
    }
}
