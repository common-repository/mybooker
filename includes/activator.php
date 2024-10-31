<?php
/**
 * Plugin Name: MyBooker
 * Plugin URI: https://bracketssquare.com/mybooker
 * Description: MyBooker is a free scheduling plugin for WordPress that allows accepting online bookings on your website.
 * Version: 0.1.0
 * Author: Brackets Square
 * Author URI: https://bracketssquare.com/
 * Text Domain: mybooker
 * Domain Path: /languages/
 *
 * License: GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

class Mybooker_Activator
{
    public function __construct()
    {
        // Activation hook
    }
    public static function jal_install()
    {
        global $wpdb;
        $version = get_option('mybooker_plugin_version', '1.0');
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'booking_entries';

        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          item_id smallint(5) NOT NULL,
          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          booking_name tinytext NOT NULL,
          booking_email tinytext NOT NULL,
          booking_note longtext NULL,
          booking_type tinytext NULL,
          booking_datetime longtext NOT NULL,
          form_value longtext NOT NULL,
          pm_method tinytext NULL,
          pm_transaction tinytext NULL,
          status smallint(5) NOT NULL,
          et_status tinytext NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        if (version_compare($version, '2.0') < 0) {
            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            item_id smallint(5) NOT NULL,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            booking_name tinytext NOT NULL,
            booking_email tinytext NOT NULL,
            booking_note longtext NULL,
            booking_type tinytext NULL,
            booking_datetime longtext NOT NULL,
            form_value longtext NOT NULL,
            pm_method tinytext NULL,
            pm_transaction tinytext NULL,
            status smallint(5) NOT NULL,
            et_status tinytext NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";
            dbDelta($sql);

            update_option('mybooker_plugin_version', '2.0');
        }
    }

    public static function add_option() {
        $settings = get_option('mybooker_settings');

        if(empty($settings)) {
            $default = array(
                'booking_na_text'     => array('short' => esc_html__('N/A', 'mybooker'),
                                               'long' => esc_html__('Unavailable', 'mybooker')),
                'booking_style_enable' => true,
                'booking_email_from' => 'mail@bracketssquare.com',
                'booking_email_cc' => 'mail@bracketssquare.com',
                'booking_email_name' => esc_html__('Brackets Square', 'mybooker'),
                'booking_email_subject' => esc_html__('This is booking notification', 'mybooker'),
                'booking_email_message' => '',
                'booking_payment_stripe_api_key' => '',
                'booking_payment_stripe_api_secret' => '',
                'booking_payment_stripe_test_mode' => true,
                'booking_payment_stripe_api_test_key' => '',
                'booking_payment_stripe_api_test_secret' => '',
                'booking_currency_type' => esc_html__('usd', 'mybooker'), 
            );
            update_option( 'mybooker_settings', $default );
        }
    }
}
