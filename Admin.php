<?php
/**
 * Admin – registers the admin menu and enqueues React assets.
 *
 * @package WcWan\Admin
 */

namespace WcWan\Admin;

defined( 'ABSPATH' ) || exit;

class Admin {

    public function register_menu(): void {
        $wa_icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>' );

        add_menu_page(
            __( 'WA Order Notification', 'orderzap-notifications' ),
            __( 'WA Notify', 'orderzap-notifications' ),
            'manage_woocommerce',
            'wc-wan',
            [ $this, 'render_page' ],
            $wa_icon,
            56
        );

        add_submenu_page( 'wc-wan', __( 'Dashboard', 'orderzap-notifications' ), __( 'Dashboard', 'orderzap-notifications' ), 'manage_woocommerce', 'wc-wan',                  [ $this, 'render_page' ] );
        add_submenu_page( 'wc-wan', __( 'Settings',  'orderzap-notifications' ), __( 'Settings',  'orderzap-notifications' ), 'manage_woocommerce', 'wc-wan-settings',         [ $this, 'render_page' ] );
        add_submenu_page( 'wc-wan', __( 'Templates', 'orderzap-notifications' ), __( 'Templates', 'orderzap-notifications' ), 'manage_woocommerce', 'wc-wan-templates',        [ $this, 'render_page' ] );
        add_submenu_page( 'wc-wan', __( 'Logs',      'orderzap-notifications' ), __( 'Logs',      'orderzap-notifications' ), 'manage_woocommerce', 'wc-wan-logs',             [ $this, 'render_page' ] );
    }

    public function render_page(): void {
        echo '<div id="wc-wan-app"></div>';
    }

    public function enqueue_assets( string $hook ): void {
        $pages = [
            'toplevel_page_wc-wan',
            'wa-notify_page_wc-wan-settings',
            'wa-notify_page_wc-wan-templates',
            'wa-notify_page_wc-wan-logs',
        ];
        if ( ! in_array( $hook, $pages, true ) ) {
            return;
        }

        wp_enqueue_script( 'wc-wan-admin', WC_WAN_URL . 'assets/js/admin.js', [], WC_WAN_VERSION, true );
        wp_enqueue_style(  'wc-wan-admin', WC_WAN_URL . 'assets/css/admin.css', [], WC_WAN_VERSION );

        wp_localize_script( 'wc-wan-admin', 'wcWan', [
            'apiBase'   => esc_url_raw( rest_url( 'wc-wan/v1' ) ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'version'   => WC_WAN_VERSION,
            'adminUrl'  => admin_url( 'admin.php' ),
            'siteTitle' => get_bloginfo( 'name' ),
            'page'      => sanitize_key( $_GET['page'] ?? 'wc-wan' ), // phpcs:ignore WordPress.Security.NonceVerification
        ] );
    }

    public function plugin_action_links( array $links ): array {
        $custom = [ '<a href="' . esc_url( admin_url( 'admin.php?page=wc-wan-settings' ) ) . '">' . __( 'Settings', 'orderzap-notifications' ) . '</a>' ];
        return array_merge( $custom, $links );
    }
}
