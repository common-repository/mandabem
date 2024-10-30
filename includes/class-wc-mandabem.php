<?php


if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WC_MANDABEM_PATH')) {
    define('WC_MANDABEM_PATH', dirname(dirname(__FILE__)));
}

class WC_Mandabem {

    private $url_base = "https://mandabem.com.br/ws/";

    public static function start_plugin() {

        add_action('init', array(__CLASS__, 'load_plugin_textdomain'), -1);

        // Checks with WooCommerce is installed.
        if (class_exists('WC_Integration')) {

            include_once WC_MANDABEM_PATH . '/includes/wc-mandabem-functions.php';
            include_once WC_MANDABEM_PATH . '/includes/class-wc-mandabem-integration.php';
            include_once WC_MANDABEM_PATH . '/includes/class-wc-mandabem-webservice.php';
            include_once WC_MANDABEM_PATH . '/includes/class-wc-mandabem-cart.php';

            // Methods
            include_once WC_MANDABEM_PATH . '/includes/methods/class-wc-mandabem-shipping-abstract.php';
            include_once WC_MANDABEM_PATH . '/includes/methods/class-wc-mandabem-pacmini.php';
            include_once WC_MANDABEM_PATH . '/includes/methods/class-wc-mandabem-pac.php';
            include_once WC_MANDABEM_PATH . '/includes/methods/class-wc-mandabem-sedex.php';

            // add gerenciador de pedidos
//            if (is_admin()) {
            include_once WC_MANDABEM_PATH . '/includes/class-wc-mandabem-orders.php';
//            }

            add_filter('woocommerce_integrations', array(__CLASS__, 'include_integrations'));
            add_filter('woocommerce_shipping_methods', array(__CLASS__, 'include_methods'));
//            add_filter('woocommerce_mandabem_generate_shipping_package', array(__CLASS__, 'generate_shipping_package'));
        } else {
            add_action('admin_notices', array(__CLASS__, 'woocommerce_missing_notice'));
        }
    }

    public static function include_integrations($integrations) {
        $integrations[] = 'WC_Mandabem_Integration';

        return $integrations;
    }

    public static function include_methods($methods) {
        // Legacy method.
//        $methods['mandabem-legacy'] = 'WC_Mandabem_Shipping_Legacy';
        // New methods.
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.6.0', '>=')) {
            $methods['mandabem-pacmini'] = 'WC_Mandabem_Shipping_PACMINI';
            $methods['mandabem-pac'] = 'WC_Mandabem_Shipping_PAC';
            $methods['mandabem-sedex'] = 'WC_Mandabem_Shipping_SEDEX';
        }

        return $methods;
    }

    public static function load_plugin_textdomain() {
        load_plugin_textdomain('mandabem', false, WC_MANDABEM_PATH . '/languages/');
    }

    public static function get_url_base() {
        return $this->url_base;
    }

    /**
     * WooCommerce dependecy notice.
     */
    public static function woocommerce_missing_notice() {
        include_once MC_MANDABEM_PATH . '/includes/views/missing-dependencies.php';
    }

//    public static function generate_shipping_package($order) {
//        
//    }
    
}
