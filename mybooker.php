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

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mybooker')) :

class Mybooker
{
    private static $instance;

    public static function instance()
    {
        if (! isset(self::$instance) && ! (self::$instance instanceof Mybooker)) {
            self::$instance = new Mybooker;

            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Constants
     */
    public function constants()
    {

        // Plugin version
        if (! defined('MY_BOOKER_PLUGIN_VERSION')) {
            define('MY_BOOKER_VERSION', '0.1.0');
        }

        // Plugin file
        if (! defined('MY_BOOKER_PLUGIN_FILE')) {
            define('MY_BOOKER_PLUGIN_FILE', __FILE__);
        }

        // Plugin basename
        if (! defined('MY_BOOKER_PLUGIN_BASENAME')) {
            define('MY_BOOKER_PLUGIN_BASENAME', plugin_basename(MY_BOOKER_PLUGIN_FILE));
        }

        // Plugin directory path
        if (! defined('MY_BOOKER_PLUGIN_DIR_PATH')) {
            define('MY_BOOKER_PLUGIN_DIR_PATH', trailingslashit(plugin_dir_path(MY_BOOKER_PLUGIN_FILE)));
        }

        // Plugin directory URL
        if (! defined('MY_BOOKER_PLUGIN_DIR_URL')) {
            define('MY_BOOKER_PLUGIN_DIR_URL', trailingslashit(plugin_dir_url(MY_BOOKER_PLUGIN_FILE)));
        }

        // Templates directory
        if (! defined('MY_BOOKER_PLUGIN_TEMPLATES_DIR_PATH')) {
            define('MY_BOOKER_PLUGIN_TEMPLATES_DIR_PATH', MY_BOOKER_PLUGIN_DIR_PATH . 'templates/');
        }
    }

    /**
     * Include/Require PHP files
     */
    public function includes()
    {
        require_once(sprintf("%s/includes/booking.php", MY_BOOKER_PLUGIN_DIR_PATH));
        require_once(sprintf("%s/includes/booking-item.php", MY_BOOKER_PLUGIN_DIR_PATH));
				require_once(sprintf("%s/includes/booking-entry.php", MY_BOOKER_PLUGIN_DIR_PATH));
        require_once(sprintf("%s/includes/booking-email.php", MY_BOOKER_PLUGIN_DIR_PATH));
        require_once(sprintf("%s/includes/activator.php", MY_BOOKER_PLUGIN_DIR_PATH));

        if ( is_admin() ) {
          require_once(sprintf("%s/includes/admin.php", MY_BOOKER_PLUGIN_DIR_PATH));
        }
    }

    /**
     * Action/filter hooks
     */
    public function hooks()
    {
        $activator = new Mybooker_Activator();

        register_activation_hook(MY_BOOKER_PLUGIN_FILE, array( $activator, 'jal_install' ));
        register_activation_hook(MY_BOOKER_PLUGIN_FILE, array( $activator, 'add_option' ));

        add_action( 'init', array( 'Mybooker_Form', 'mybooker_register_post_type') );
        add_action('init', 'do_output_buffer');

        function do_output_buffer() {
                ob_start();
        }

        add_action('plugins_loaded', array( $this, 'loaded' ));

        add_filter('plugin_row_meta', array( $this, 'plugin_row_links' ), 10, 2);
        add_filter('plugin_action_links_' . MY_BOOKER_PLUGIN_BASENAME, array( $this, 'action_links' ));
    }

    /**
     * Run on plugin activation
     */
    public function activate()
    {
    }

    /**
     * Load plugin text domain
     */
    public function loaded()
    {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();

        $locale = apply_filters('plugin_locale', $locale, 'mybooker');
        unload_textdomain('mybooker');
        load_textdomain('mybooker', dirname(MY_BOOKER_PLUGIN_BASENAME) . '/languages/' . $locale . '.mo');

        load_plugin_textdomain('mybooker', false, dirname(MY_BOOKER_PLUGIN_BASENAME) . '/languages');

        //add shortcode
        add_shortcode('mybooker_form', 'mybooker_form_shortcode' );
        add_shortcode('mybooker_entries', 'mybooker_member_entries' );

        //enquere script
        add_action('wp_enqueue_scripts', array('Mybooker_Form','mybooker_enqueue_script'));
        add_action('wp_print_styles', array('Mybooker_Form','mybooker_enqueue_css'), 99 );
    }
    /**
     * Plugin action links (under plugin's Name)
     * @param  array 	$links 	Current links
     * @return array        New links
     */
    // public function action_links($links)
    // {
    //     $links[] = sprintf('<a href="%s" aria-label="%s">%s</a>', admin_url( 'admin.php?page=mybooker_settings' ) , esc_html__('Settings', 'mybooker'), esc_html__('Settings', 'mybooker'));
    //
    //     return $links;
    // }

    /**
     * Plugin info row links (under plugin's Description)
     * @param  array 	$links 	Current links
     * @param  string 	$file  	Plugin basename
     * @return array        	New links
     */
    public function plugin_row_links($links, $file)
    {
        if ($file == plugin_basename(__FILE__)) {
            $links[] = sprintf('<a href="%s" target="_blank">%s</a>', esc_url('http://bracketssquare.com/'), 'Get support');
        }
        return $links;
    }
}

endif;

/**
 * Main function
 *
 * @return object 	Mybooker instance
 */
function mybooker()
{
    return Mybooker::instance();
}

mybooker();
