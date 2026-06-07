<?php
namespace Woolentor\Modules\EmailReports;

/**
 * Report Generator class
 */
class Report_Generator {

    /** @var array|null */
    private $current_orders_cache = null;

    /** @var array|null */
    private $previous_orders_cache = null;

    /**
     * Generate report data
     */
    public function generate() {
        $metrics = woolentor_get_option('report_metrics', 'woolentor_email_reports_settings', array());
        $data = array(
            'period_start' => $this->get_period_start(),
            'period_end'   => $this->get_period_end(),
            'schedule_type' => woolentor_get_option('schedule_type', 'woolentor_email_reports_settings', 'daily')
        );

        // Get sales data
        if(in_array('sales', $metrics)) {
            $data['sales'] = $this->get_sales_data();
            $data['previous_sales'] = $this->get_sales_data(true); // For comparison
        }

        // Get orders data
        if(in_array('orders', $metrics)) {
            $data['orders'] = $this->get_orders_data();
            $data['previous_orders'] = $this->get_orders_data(true); // For comparison
        }

        // Get top products
        if(in_array('top_products', $metrics)) {
            $data['top_products'] = $this->get_top_products();
        }

        return $data;
    }

    /**
     * Get period start date
     */
    private function get_period_start() {
        $schedule = woolentor_get_option('schedule_type', 'woolentor_email_reports_settings', 'daily');
        $current_time = current_time('timestamp');

        switch($schedule) {
            case 'custom':
                $minutes = (int) woolentor_get_option('custom_minutes', 'woolentor_email_reports_settings', 30);
                return date('Y-m-d H:i:s', strtotime("-{$minutes} minutes", $current_time));

            case 'hourly':
                return date('Y-m-d H:i:s', strtotime('-1 hour', $current_time));

            case 'daily':
                return date('Y-m-d H:i:s', strtotime('-1 day', $current_time));

            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('-7 days', $current_time));

            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('-30 days', $current_time));

            default:
                return date('Y-m-d H:i:s', strtotime('-1 day', $current_time));
        }
    }

    /**
     * Get period end date
     */
    private function get_period_end() {
        return current_time('Y-m-d H:i:s');
    }

    /**
     * Get previous period start date (for comparison)
     */
    private function get_previous_period_start() {
        $schedule = woolentor_get_option('schedule_type', 'woolentor_email_reports_settings', 'daily');
        $current_start = strtotime($this->get_period_start());

        switch($schedule) {
            case 'custom':
                $minutes = (int) woolentor_get_option('custom_minutes', 'woolentor_email_reports_settings', 30);
                return date('Y-m-d H:i:s', strtotime("-{$minutes} minutes", $current_start));

            case 'hourly':
                return date('Y-m-d H:i:s', strtotime('-1 hour', $current_start));

            case 'daily':
                return date('Y-m-d H:i:s', strtotime('-1 day', $current_start));

            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('-7 days', $current_start));

            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('-30 days', $current_start));

            default:
                return date('Y-m-d H:i:s', strtotime('-1 day', $current_start));
        }
    }

    /**
     * Fetch orders for an arbitrary date range via the WooCommerce order data store.
     * Works on both legacy (wp_posts) and HPOS (wp_wc_orders) storage.
     */
    private function get_orders_for_period( $start_date, $end_date ) {
        return wc_get_orders( [
            'type'       => 'shop_order',
            'status'     => [ 'wc-completed', 'wc-processing' ],
            'limit'      => -1,
            'date_query' => [
                [
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ],
            ],
        ] );
    }

    private function get_current_orders() {
        if ( $this->current_orders_cache === null ) {
            $this->current_orders_cache = $this->get_orders_for_period(
                $this->get_period_start(),
                $this->get_period_end()
            );
        }
        return $this->current_orders_cache;
    }

    private function get_previous_orders() {
        if ( $this->previous_orders_cache === null ) {
            $this->previous_orders_cache = $this->get_orders_for_period(
                $this->get_previous_period_start(),
                $this->get_period_start()
            );
        }
        return $this->previous_orders_cache;
    }

    /**
     * Get sales data
     */
    private function get_sales_data( $previous_period = false ) {
        $orders = $previous_period ? $this->get_previous_orders() : $this->get_current_orders();
        $total  = 0.0;
        foreach ( $orders as $order ) {
            $total += (float) $order->get_total();
        }
        return $total;
    }

    /**
     * Get orders data
     */
    private function get_orders_data( $previous_period = false ) {
        $orders = $previous_period ? $this->get_previous_orders() : $this->get_current_orders();
        return count( $orders );
    }

    /**
     * Get top products
     */
    private function get_top_products() {
        $aggregated = [];

        foreach ( $this->get_current_orders() as $order ) {
            foreach ( $order->get_items() as $item ) {
                $product_id = (int) $item->get_product_id();
                if ( ! $product_id ) {
                    continue;
                }
                if ( ! isset( $aggregated[ $product_id ] ) ) {
                    $aggregated[ $product_id ] = [ 'quantity' => 0, 'revenue' => 0.0 ];
                }
                $aggregated[ $product_id ]['quantity'] += (int) $item->get_quantity();
                $aggregated[ $product_id ]['revenue']  += (float) $item->get_total();
            }
        }

        uasort( $aggregated, function ( $a, $b ) {
            return $b['quantity'] <=> $a['quantity'];
        } );

        $results = [];
        foreach ( array_slice( $aggregated, 0, 5, true ) as $product_id => $data ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $previous_data = $this->get_product_previous_data( $product_id );

            $obj                  = new \stdClass();
            $obj->ID              = $product_id;
            $obj->post_title      = $product->get_name();
            $obj->quantity        = $data['quantity'];
            $obj->revenue         = $data['revenue'];
            $obj->quantity_change = $this->calculate_percentage_change(
                $previous_data->quantity ?? 0,
                $obj->quantity
            );
            $obj->revenue_change  = $this->calculate_percentage_change(
                $previous_data->revenue ?? 0,
                $obj->revenue
            );

            $results[] = $obj;
        }

        return $results;
    }

    /**
     * Get product data for previous period
     */
    private function get_product_previous_data( $product_id ) {
        $quantity = 0;
        $revenue  = 0.0;

        foreach ( $this->get_previous_orders() as $order ) {
            foreach ( $order->get_items() as $item ) {
                if ( (int) $item->get_product_id() === (int) $product_id ) {
                    $quantity += (int) $item->get_quantity();
                    $revenue  += (float) $item->get_total();
                }
            }
        }

        $obj           = new \stdClass();
        $obj->quantity = $quantity;
        $obj->revenue  = $revenue;
        return $obj;
    }

    /**
     * Calculate percentage change
     */
    private function calculate_percentage_change($old_value, $new_value) {
        if($old_value == 0) {
            return $new_value > 0 ? 100 : 0;
        }
        return (($new_value - $old_value) / $old_value) * 100;
    }
}