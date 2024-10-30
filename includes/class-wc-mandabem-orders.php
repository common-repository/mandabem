<?php

/**
 * Integração dos pedidos
 * 
 */
class WC_Mandabem_Orders {

    /**
     * Logger.
     *
     * @var WC_Logger
     */
    protected $log = null;

    public function __construct() {

        add_filter('woocommerce_order_actions', array($this, 'view_order_action'), 10, 2);
        add_action( 'woocommerce_order_action_send_order_mandabem', array($this, 'process_status'), 100, 1 );
        
        
        add_action('woocommerce_order_status_changed', array($this, 'process_status'), 100, 1);
        add_action('woocommerce_hidden_order_itemmeta', array($this, 'custom_woocommerce_hidden_order_itemmeta'), 1, 1);
        add_action('woocommerce_thankyou', array($this, 'process_status'), 10);

        add_filter('woocommerce_order_shipping_method', array($this, 'shipping_method'), 100, 2);
        add_filter('woocommerce_order_item_display_meta_key', array($this, 'item_display_delivery'), 100, 2);
    }

    function view_order_action($actions) {
        $actions['send_order_mandabem'] =  __('Gerar Envio Manda Bem', 'woocommerce-mandabem'); 
        return $actions;
    }  

    public function process_status($order, $checkout = null) {
        
        $gerar = true;
        if(is_int($order)){
            // Vem das mudancas de status do pedido
            $order = new WC_Order($order);
            $gerar = false;
        } 
        
        //$this->log = new WC_Logger();

        // Get Products Info
        $items = $order->get_items();
        $_pf = new WC_Product_Factory();
        $products = array();
        foreach ($items as $i) {
            $products[] = array(
                'nome' => $i->get_name(),
                'quantidade' => $i->get_quantity(),
                'preco' => $_pf->get_product($i->get_product_id()) ? $_pf->get_product($i->get_product_id())->get_price() : 0
            );
        }
        // End Products Info

        $current_status = 'wc-' . $order->status;
        $status_to_post = apply_filters('woocommerce_mandabem_get_status_post_envio', 'wc-completed'); 
        $status_envio   = apply_filters('woocommerce_mandabem_status_post_envio_package', 'SEDEX'); 
        if ($current_status === $status_to_post || $gerar) {
            $user_data = apply_filters('woocommerce_mandabem_credentials_data', array(
                'store_id' => '',
                'store_toke' => '',
                'store_status_envio'=> '',
            )); 

            $api = new WC_Mandabem_Webservice($this->id, $this->instance_id);
            $api->set_debug($this->debug);
            $api->set_store_id($user_data['store_id']);
            $api->set_store_token($user_data['store_token']);

            

            $shipping_method = array_shift($order->get_shipping_methods());
            $shipping_method_id = $shipping_method['method_id'];
            $forma_envio = null;
            if ($shipping_method_id === 'mandabem-sedex') {
                $forma_envio = 'SEDEX';
            }
            if ($shipping_method_id === 'mandabem-pac') {
                $forma_envio = 'PAC';
            }
            if ($shipping_method_id === 'mandabem-pacmini') {
                $forma_envio = 'PACMINI';
            }

            if ($shipping_method_id === 'free_shipping') {
                $forma_envio = $status_envio;
            } 

            if (!$forma_envio) {
                return;
            }

            $peso = 0;
            $altura = 0;
            $largura = 0;
            $comprimento = 0;
            foreach ($order->get_data('shipping')['shipping_lines'] as $i) {
                if (method_exists($i, 'get_meta_data')) {
                    $meta_data = $i->get_meta_data();
                    foreach ($meta_data as $m) {
                        $keys = $m->get_data();
                        if ($keys['key'] === '_delivery_mandabem_peso') {
                            $peso = $keys['value'];
                        }
                        if ($keys['key'] === '_delivery_mandabem_altura') {
                            $altura = $keys['value'];
                        }
                        if ($keys['key'] === '_delivery_mandabem_largura') {
                            $largura = $keys['value'];
                        }
                        if ($keys['key'] === '_delivery_mandabem_comprimento') {
                            $comprimento = $keys['value'];
                        }
                    }
                }
            }

            $data_send = array(
                'endpoint' => 'gerar_envio',
                'forma_envio' => $forma_envio,
                'cep_origem' => apply_filters('woocommerce_mandabem_get_origin_postcode', ''),
                'destinatario' => $order->get_address('shipping')['first_name'] . ' ' . $order->get_address('shipping')['last_name'],
                'cpf_destinatario' => $order->billing_cpf,
                'email' => $order->get_billing_email(),
                'cep' => preg_replace('/[^0-9]/', '', $order->get_address('shipping')['postcode']),
                'logradouro' => $order->get_address('shipping')['address_1'],
                'numero' => $order->get_address('shipping')['number'],
                'complemento' => $order->get_address('shipping')['address_2'],
                'bairro' => $order->get_address('shipping')['neighborhood'],
                'cidade' => $order->get_address('shipping')['city'],
                'estado' => $order->get_address('shipping')['state'],
                'peso' => $peso,
                'altura' => $altura,
                'largura' => $largura,
                'comprimento' => $comprimento,
                'ref_id' => $order->id,
                'produtos' => $products 
            ); 

            $response = $api->send_post($data_send);


            if (!$response || !isset($response->resultado)) {
                $note = __("Falha ao gerar envio Mandabem, contate suporte (1).");
                $order->add_order_note($note, false);
                $order->save();
            } else {
                if ($response->resultado->sucesso === 'true') {
                    $note = __("Envio N. " . $response->resultado->envio_id . " gerado com sucesso!");
                    $order->add_order_note($note, false);
                    $order->save();
                } else {
                    if (isset($response->resultado->error)) {
                        $note = __("Falha ao gerar envio Mandabem:<br>\n" . $response->resultado->error);
                    } else {
                        $note = __("Falha ao gerar envio Mandabem, contate suporte.");
                    }
                    $order->add_order_note($note, false);
                    $order->save();
                }
            }
        }
    }

    public function register_metabox() {
        add_meta_box(
                'wc_mandabem', 'Mandabem', array($this, 'metabox_content'), 'shop_order', 'side', 'default'
        );
    }

    public function shipping_method($name, $order) {
        $names = array();

        foreach ($order->get_shipping_methods() as $shipping_method) {
            $total = (int) $shipping_method->get_meta('_delivery_mandabem');

            if ($total) {
                $names[] = sprintf(_n('%1$s (Prazo entre %2$d dia útil após confirmação)', '%1$s (Prazo entre %2$d dias úteis após confirmação)', $total, 'woocommerce-mandabem'), $shipping_method->get_name(), $total);
            } else {
                $names[] = $shipping_method->get_name();
            }
        }

        return implode(', ', $names);
    }

    public function custom_woocommerce_hidden_order_itemmeta($arr) {
        
        $arr[] = '_delivery_mandabem_altura';
        $arr[] = '_delivery_mandabem_largura';
        $arr[] = '_delivery_mandabem_comprimento';
        return $arr;
    }
    
    public  $id = '' ;

    public function item_display_delivery($display_key, $meta) {

        if ($meta->key === '_delivery_mandabem') {
            return __('Prazo (dias)', 'woocommerce-mandabem');
        } else if ($meta->key === '_delivery_mandabem_peso') {
            return __('Peso (Kg)', 'woocommerce-mandabem');
        }else if ($meta->key === '_delivery_mandabem_rastreio') {
            return __('Rastreio', 'woocommerce-mandabem');
        }else if ($meta->key === '_delivery_mandabem_entrega') {
            return __('Entrega', 'woocommerce-mandabem');
        } else {
            return $display_key;
        }
             
    }

}
//_delivery_mandabem_altura
new WC_Mandabem_Orders();
