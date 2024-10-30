<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Mandabem_Cart {

    public function __construct() {
        add_action('woocommerce_after_shipping_rate', array($this, 'shipping_delivery_mandabem'), 100);
    }

    public function shipping_delivery_mandabem($shipping_method) {
        
        $meta_data = $shipping_method->get_meta_data();
        $total = isset($meta_data['_delivery_mandabem']) ? intval($meta_data['_delivery_mandabem']) : 0;

        if ($total) {
            echo '<p><small>' . esc_html(sprintf(_n('(Entrega em %d dia útil)', '(Entrega em %d dias úteis)', $total, 'woocommerce-mandabem'), $total)) . '</small></p>';
        }
    }

}

new WC_Mandabem_Cart();
