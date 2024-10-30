<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Mandabem_Shipping_PAC extends WC_Mandabem_Shipping {
    
    protected $code = 'PAC';
    
    public function __construct($instance_id = 0) {
        $this->id = 'mandabem-pac';
        $this->method_title = __('PAC Promocional', 'woocommerce-mandabem');
        $this->more_link = 'http://www.correios.com.br/enviar-e-receber/encomendas';

        parent::__construct($instance_id);
    }

    protected function get_declared_value($package) {
        return $package['contents_cost'];
    }

}
