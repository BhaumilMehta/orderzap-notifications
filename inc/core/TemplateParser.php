<?php
/**
 * TemplateParser – replaces dynamic variables in message templates.
 *
 * Order total fix: use number_format on the raw float so WhatsApp
 * receives a plain Unicode string (₹125.50) rather than HTML entities
 * (&#8377;125.50) from wc_price() which returns HTML.
 *
 * @package WcWan\Core
 */

namespace WcWan\Core;

defined( 'ABSPATH' ) || exit;

class TemplateParser {

    public static function supported_variables(): array {
        return [
            '{customer_name}',
            '{order_id}',
            '{order_total}',
            '{order_status}',
            '{order_url}',
            '{tracking_number}',
            '{courier_name}',
            '{tracking_url}',
            '{site_name}',
            '{note_message}',
            '{billing_phone}',
        ];
    }

    public function parse( string $template, \WC_Order $order, array $extra = [] ): string {
        $vars = $this->build_vars( $order, $extra );
        return str_replace( array_keys( $vars ), array_values( $vars ), $template );
    }

    private function build_vars( \WC_Order $order, array $extra ): array {
        $customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        if ( empty( $customer_name ) ) {
            $customer_name = $order->get_billing_email();
        }

        $vars = [
            '{customer_name}'   => $customer_name,
            '{order_id}'        => (string) $order->get_id(),
            '{order_total}'     => $this->format_order_total( $order ),
            '{order_status}'    => wc_get_order_status_name( $order->get_status() ),
            '{order_url}'       => $order->get_view_order_url(),
            '{billing_phone}'   => $order->get_billing_phone(),
            '{site_name}'       => get_bloginfo( 'name' ),
            '{tracking_number}' => '',
            '{courier_name}'    => '',
            '{tracking_url}'    => '',
            '{note_message}'    => '',
        ];

        foreach ( $extra as $key => $value ) {
            $clean_key          = '{' . trim( $key, '{}' ) . '}';
            $vars[ $clean_key ] = sanitize_text_field( (string) $value );
        }

        return $vars;
    }

    /**
     * Format order total as a plain Unicode string suitable for WhatsApp.
     *
     * wc_price() returns HTML (e.g. <span class="woocommerce-Price-amount">
     * <bdi><span class="woocommerce-Price-currencySymbol">&#8377;</span>125.50</bdi></span>)
     * which renders as raw HTML in a WhatsApp message.
     *
     * Instead we:
     * 1. Get the raw currency symbol (e.g. ₹) from WC settings.
     * 2. Format the float with the store's decimal + thousand separators.
     * 3. Apply WC currency_pos setting (left / right / left_space / right_space).
     */
    private function format_order_total( \WC_Order $order ): string {
        $amount   = (float) $order->get_total();
        $currency = $order->get_currency();

        // Raw Unicode symbol, e.g. ₹  $  €
        $symbol = html_entity_decode(
            get_woocommerce_currency_symbol( $currency ),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $decimals          = (int) get_option( 'woocommerce_price_num_decimals', 2 );
        $decimal_sep       = get_option( 'woocommerce_price_decimal_sep', '.' );
        $thousand_sep      = get_option( 'woocommerce_price_thousand_sep', ',' );
        $currency_pos      = get_option( 'woocommerce_currency_pos', 'left' );

        $formatted_number = number_format( $amount, $decimals, $decimal_sep, $thousand_sep );

        switch ( $currency_pos ) {
            case 'left':
                return $symbol . $formatted_number;
            case 'right':
                return $formatted_number . $symbol;
            case 'left_space':
                return $symbol . ' ' . $formatted_number;
            case 'right_space':
                return $formatted_number . ' ' . $symbol;
            default:
                return $symbol . $formatted_number;
        }
    }
}
