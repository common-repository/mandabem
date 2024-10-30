<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Mandabem_Shipping_PACMINI extends WC_Mandabem_Shipping {
    
    protected $code = 'PACMINI';
    
    public function __construct($instance_id = 0) {
        $this->id = 'mandabem-pacmini';
        $this->method_title = __('Envio Mini', 'woocommerce-mandabem');
        $this->more_link = 'http://www.correios.com.br/enviar-e-receber/encomendas';

        parent::__construct($instance_id);
    }

    protected function get_declared_value($package) {
        return $package['contents_cost'];
    }

}
