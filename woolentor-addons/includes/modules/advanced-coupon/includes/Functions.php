<?php
namespace Woolentor\Modules\AdvancedCoupon;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Functions{

    /**
     * Get Meta data
     * @param mixed $id
     * @param mixed $meta_key
     * @return mixed
     */
    public static function get_meta_data($id, $meta_key, $default = ""){
        if (!metadata_exists('post', $id, $meta_key)) {
            return $default;
        }else{
            $meta_data = get_post_meta($id, $meta_key, true);
            return $meta_data;
        }
    }

    /**
     * Multiple Data fetching
     * @param mixed $id
     * @param mixed $meta_key
     * @param mixed $default
     * @return array
     */
    public static function get_multiple_meta_date($id, $meta_key, $default = ''){
        $meta_data = self::get_meta_data($id, $meta_key, $default);
        $meta_data = (is_string($meta_data) && $meta_data ? array_filter(explode(',', $meta_data)) : $meta_data);
        
        return (!is_array($meta_data) ? [] : $meta_data);
    }

    /**
     * Creates a WC_DateTime object from various date inputs (timestamp, string, or WC_DateTime object).
     *
     * @param mixed $date The input date, which can be a timestamp, a date string, or a WC_DateTime object.
     * @return \WC_DateTime|null A WC_DateTime object on success, or null on failure.
     */
    public static function create_datetime($date) {
        try {
            if (empty($date)) {
                return null;
            }
    
            // If it's already a WC_DateTime object, return it directly.
            if ($date instanceof \WC_DateTime) {
                return $date;
            }
    
            if (is_numeric($date)) {
                // Handle numeric timestamps (UTC).
                $datetime = new \WC_DateTime("@{$date}", new \DateTimeZone('UTC'));
            } else {
                // Handle string-based dates.
                $timestamp = self::parse_date_to_timestamp($date);
                if ($timestamp === false) {
                    return null; // Invalid date string, return null.
                }
                $datetime = new \WC_DateTime("@{$timestamp}", new \DateTimeZone('UTC'));
            }
    
            // Apply local timezone settings.
            if ( get_option('timezone_string') ) {
                $datetime->setTimezone(new \DateTimeZone(wc_timezone_string()));
            } else {
                $datetime->set_utc_offset(wc_timezone_offset());
            }
    
            return $datetime;
        } catch (\Exception $e) {
            // Suppress exception and return null on failure.
            return null;
        }
    }
    
    /**
     * Parse a date string into a timestamp.
     *
     * @param string $date The date string.
     * @return int|false The timestamp, or false on failure.
     */
    private static function parse_date_to_timestamp($date) {
        // Check for ISO8601 format and extract the timestamp.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|([-+]\d{2}:\d{2}))?$/', $date, $matches)) {
            $offset = !empty($matches[7]) 
                ? iso8601_timezone_to_offset($matches[7]) 
                : wc_timezone_offset();
    
            return gmmktime(
                $matches[4], $matches[5], $matches[6], 
                $matches[2], $matches[3], $matches[1]
            ) - $offset;
        }
    
        // Handle other string formats using WooCommerce helper.
        $gmt_date = get_gmt_from_date(gmdate('Y-m-d H:i:s', wc_string_to_timestamp($date)));
        return wc_string_to_timestamp($gmt_date);
        
    }

    /**
     * Prepares a timestamp from a WC_DateTime object.
     * @param mixed $date The input date, which can be a WC_DateTime object, timestamp, or string.
     * @param bool $gmt Optional. Whether to return the GMT timestamp. Default: true.
     * @return int The corresponding timestamp, or 0 if the date is invalid.
     */
    public static function get_date_timestamp($date, $gmt = true) {
        // Convert the input date to a WC_DateTime object.
        $datetime = self::create_datetime($date);

        // If the conversion was successful, return the appropriate timestamp.
        if ($datetime instanceof \WC_DateTime) {
            return $gmt ? $datetime->getOffsetTimestamp() : $datetime->getTimestamp();
        }

        // Return 0 if the input was invalid or conversion failed.
        return 0;
    }


}