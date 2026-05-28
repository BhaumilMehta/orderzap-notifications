<?php
/**
 * Logger – stores WhatsApp notification log entries.
 *
 * @package WcWan\Logs
 */

namespace WcWan\Logs;

defined( 'ABSPATH' ) || exit;

class Logger {

    public function log( array $data ): void {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'wc_wan_logs',
            [
                'order_id'   => absint( $data['order_id'] ?? 0 ),
                'event'      => sanitize_key( $data['event'] ?? '' ),
                'recipient'  => sanitize_text_field( $data['recipient'] ?? '' ),
                'message'    => wp_kses_post( $data['message'] ?? '' ),
                'status'     => sanitize_key( $data['status'] ?? 'failed' ),
                'message_id' => sanitize_text_field( $data['message_id'] ?? '' ),
                'error'      => sanitize_text_field( $data['error'] ?? '' ),
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
    }

    public function get_logs( array $args = [] ): array {
        global $wpdb;
        $args   = wp_parse_args( $args, [ 'per_page' => 20, 'page' => 1, 'status' => '', 'order_id' => 0 ] );
        $table  = $wpdb->prefix . 'wc_wan_logs';
        $where  = [ '1=1' ];
        $values = [];

        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = sanitize_key( $args['status'] );
        }
        if ( ! empty( $args['order_id'] ) ) {
            $where[]  = 'order_id = %d';
            $values[] = absint( $args['order_id'] );
        }

        $where_sql = implode( ' AND ', $where );
        $offset    = ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] );
        $limit     = absint( $args['per_page'] );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count_q = "SELECT COUNT(*) FROM `{$table}` WHERE {$where_sql}";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows_q  = "SELECT * FROM `{$table}` WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";

        if ( ! empty( $values ) ) {
            $total = (int) $wpdb->get_var( $wpdb->prepare( $count_q, ...$values ) ); // phpcs:ignore
            $rows  = $wpdb->get_results( $wpdb->prepare( $rows_q, ...[...$values, $limit, $offset] ), ARRAY_A ); // phpcs:ignore
        } else {
            $total = (int) $wpdb->get_var( $count_q ); // phpcs:ignore
            $rows  = $wpdb->get_results( $wpdb->prepare( $rows_q, $limit, $offset ), ARRAY_A ); // phpcs:ignore
        }

        return [ 'rows' => $rows ?: [], 'total' => $total, 'pages' => max( 1, (int) ceil( $total / $limit ) ) ];
    }

    public function prune( int $days = 30 ): int {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_wan_logs';
        $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)", $days ) ); // phpcs:ignore
        return $wpdb->rows_affected;
    }

    public function get_stats(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_wan_logs';
        $row   = $wpdb->get_row( "SELECT COUNT(*) AS total, SUM(status='sent') AS sent, SUM(status='failed') AS failed FROM `{$table}`", ARRAY_A ); // phpcs:ignore
        return [ 'total' => (int) ( $row['total'] ?? 0 ), 'sent' => (int) ( $row['sent'] ?? 0 ), 'failed' => (int) ( $row['failed'] ?? 0 ) ];
    }
}
