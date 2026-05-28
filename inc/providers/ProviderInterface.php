<?php
/**
 * ProviderInterface – contract for all WhatsApp providers.
 *
 * @package WcWan\Providers
 */

namespace WcWan\Providers;

defined( 'ABSPATH' ) || exit;

interface ProviderInterface {
    public function send( string $to, string $message ): array;
    public function is_configured(): bool;
    public function get_name(): string;
}
