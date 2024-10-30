<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Mandabem_Shipping extends WC_Shipping_Method {

    public function __construct($instance_id = 0) {
        $this->instance_id = absint($instance_id);
        $this->method_description = sprintf(__('%s é um método de envio dos Correios intermediado pela plataforma Mandabem.', 'woocommerce-mandabem'), $this->method_title);
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
        );

        // Load the form fields.
        $this->init_form_fields();

        // Define user set variables.
        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->origin_postcode = $this->get_option('origin_postcode');


        $this->minimum_height = $this->get_option('minimum_height');
        $this->minimum_width = $this->get_option('minimum_width');
        $this->minimum_length = $this->get_option('minimum_length');
        $this->extra_weight = $this->get_option('extra_weight');

        $this->debug = $this->get_option('debug');

        if (is_admin()) {
            add_filter('woocommerce_mandabem_get_origin_postcode', array($this, 'get_origin_postcode'), 10);
        }

        // Save admin options.
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function get_origin_postcode() {
        return $this->origin_postcode;
    }

    protected function get_base_postcode() {
        // WooCommerce 3.1.1+.
        if (method_exists(WC()->countries, 'get_base_postcode')) {
            return WC()->countries->get_base_postcode();
        }

        return '';
    }

//    public function admin_options() {
//    }

    public function init_form_fields() {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce-mandabem'),
                'type' => 'checkbox',
                'label' => __('Habilitar método de envio', 'woocommerce-mandabem'),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Título que aparece para o usuário durante o checkout.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => $this->method_title,
            ),
            'origin_postcode' => array(
                'title' => __('CEP de Origem', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('CEP de origem a partir de onde o pacote será enviado.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'placeholder' => '00000-000',
                'default' => $this->get_base_postcode(),
            ),
            'package_standard' => array(
                'title' => __('Definições dos Pacotes', 'woocommerce-mandabem'),
                'type' => 'title',
                'description' => __('Midida mímina para o pacote.', 'woocommerce-mandabem'),
                'default' => '',
            ),
            'minimum_height' => array(
                'title' => __('Altura Minima (cm)', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Altura minima. Os Correios consideram o minimo de 2cm.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '2',
            ),
            'minimum_width' => array(
                'title' => __('Largura Minima (cm)', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Largura minima. Os Correios consideram o minimo de 11cm.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '11',
            ),
            'minimum_length' => array(
                'title' => __('Comprimento Minimo (cm)', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Comprimento minimo. Os Correios consideram o minimo de 16cm.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '16',
            ),
            'extra_weight' => array(
                'title' => __('Peso Extra (kg)', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Peso extra em Kg a ser adicionado ao pacote.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '0',
            ),
            'debug' => array(
                'title' => __('Log de Depuração', 'woocommerce-mandabem'),
                'type' => 'checkbox',
                'label' => __('Habilitar Log', 'woocommerce-mandabem'),
                'default' => 'yes',
                'description' => sprintf(__('Log %s de eventos.', 'woocommerce-mandabem'), $this->method_title) . $this->get_log_link(),
            ),
        );
    }

    protected function get_log_link() {
        return ' <a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs&log_file=' . esc_attr($this->id) . '-' . sanitize_file_name(wp_hash($this->id)) . '.log')) . '">' . __('View logs.', 'woocommerce-mandabem') . '</a>';
    }

    public function calculate_shipping($package = array()) {

        if ($package['destination']['postcode'] === '' || $package['destination']['country'] !== 'BR') {
            return;
        }

        if (!isset($package['destination']['postcode']) || strlen(preg_replace('/[^0-9]/', '', $package['destination']['postcode'])) != 8) {
            return;
        }


        $shipping = $this->get_rate($package);
        
        

        if (!isset($shipping->sucesso) || $shipping->sucesso !== 'true') {
            return;
        }



        // Display Erros
        if ($shipping->sucesso !== 'true' && is_cart()) {
            $notice_type = 'notice';
            $notice = '<strong>' . $this->title . ':</strong> ' . esc_html($shipping->error);
            wc_add_notice($notice, $notice_type);
        }

        // Set the shipping rates.
        $label = $this->title;
        $valor_frete = $shipping->{$this->get_code()}->valor;

        // Display delivery.
        $meta_delivery = array();
        if (true) { // || $this->show_delivery_time === 'yes'
            $meta_delivery = array(
                '_delivery_mandabem' => intval($shipping->{$this->get_code()}->prazo),
                '_delivery_mandabem_peso' => $shipping->peso,
                '_delivery_mandabem_altura' => $shipping->altura,
                '_delivery_mandabem_largura' => $shipping->largura,
                '_delivery_mandabem_comprimento' => $shipping->comprimento,
                '_delivery_mandabem_rastreio' => " ",

            );
        }

        // Create the rate and apply filters.
        $rate = apply_filters(
                'woocommerce_mandabem_' . $this->id . '_rate', array(
            'id' => $this->id . $this->instance_id,
            'label' => $label,
            'cost' => (float) $valor_frete,
            'meta_data' => $meta_delivery,
                ), $this->instance_id, $package
        );
        $rates = apply_filters('woocommerce_mandabem_shipping_methods', array($rate), $package);

        $this->add_rate($rates[0]);
    }

    protected function get_rate($package) {
        $user_data = apply_filters('woocommerce_mandabem_credentials_data', array(
            'store_id' => '',
            'store_token' => '',
        ));
        $api = new WC_Mandabem_Webservice($this->id, $this->instance_id);
        $api->set_debug($this->debug);
        $api->set_store_id($user_data['store_id']);
        $api->set_store_token($user_data['store_token']);

        $package_data = $this->get_package_data($package);
        
        $data_send = array(
            'endpoint' => 'get_shipping_value',
            'cep_origem' => $this->filter_cep($this->origin_postcode),
            'cep_destino' => $this->filter_cep($package['destination']['postcode']),
            'altura' => $package_data['height'],
            'largura' => $package_data['width'],
            'comprimento' => $package_data['length'],
            'peso' => $package_data['weight'],
            'servico' => $this->get_code(),
            'products' => isset($package['contents']) ? $package['contents'] : []
        );
        $shipping = $api->send_post($data_send);
        
        if (isset($shipping->resultado)) {
            $shipping->resultado->peso = $package_data['weight'];
            $shipping->resultado->altura = $package_data['height'];
            $shipping->resultado->largura = $package_data['width'];
            $shipping->resultado->comprimento = $package_data['length'];
        }
        return $shipping->resultado;
    }

    public function get_package_data($package = array()) {

        if (!$package || !isset($package)) {
            $log = new WC_Logger();
            $log->add('mandabem', 'Pacote Invalido: ' . print_r($package, true));
            return false;
        }

        $count = 0;
        $height = array();
        $width = array();
        $length = array();
        $weight = array();
        
        foreach ($package['contents'] as $values) {
            
            $product = $values['data'];
            $qty = $values['quantity'];
            if ($qty > 0 && $product->needs_shipping()) {

                $_height = wc_get_dimension((float) $product->get_height(), 'cm');
                $_width = wc_get_dimension((float) $product->get_width(), 'cm');
                $_length = wc_get_dimension((float) $product->get_length(), 'cm');
                $_weight = wc_get_weight((float) $product->get_weight(), 'kg');
                
                if ($qty == 1 && count($package['contents']) == 1) {
                    return array(
                        'height' => $_height,
                        'width' => $_width,
                        'length' => $_length,
                        'weight' => $_weight
                    );
                }

                $height[$count] = $_height;
                $width[$count] = $_width;
                $length[$count] = $_length;
                $weight[$count] = $_weight;

                if ($qty > 1) {
                    $n = $count;
                    for ($x = 0; $x < $qty; $x++) {
                        $height[$n] = $_height;
                        $width[$n] = $_width;
                        $length[$n] = $_length;
                        $weight[$n] = $_weight;
                        $n++;
                    }
                    $count = $n;
                }

                $count++;
            }
        }


        $cubage = array();

        $data = array(
            'height' => array_values($height),
            'length' => array_values($length),
            'width' => array_values($width),
            'weight' => (array_sum($weight) + (float) wc_format_decimal($this->extra_weight)),
        );

        if (!empty($data['height']) && !empty($data['width']) && !empty($data['length'])) {

            $max_values = $this->get_max_values($data['height'], $data['width'], $data['length']);
            $root = $this->calculate_root($data['height'], $data['width'], $data['length'], $max_values);
            $greatest = array_search(max($max_values), $max_values, true);

            switch ($greatest) {
                case 'height' :
                    $cubage = array(
                        'height' => max($height),
                        'width' => $root,
                        'length' => $root,
                    );
                    break;
                case 'width' :
                    $cubage = array(
                        'height' => $root,
                        'width' => max($width),
                        'length' => $root,
                    );
                    break;
                case 'length' :
                    $cubage = array(
                        'height' => $root,
                        'width' => $root,
                        'length' => max($length),
                    );
                    break;

                default :
                    $cubage = array(
                        'height' => 0,
                        'width' => 0,
                        'length' => 0,
                    );
                    break;
            }
        } else {
            $cubage = array(
                'height' => 0,
                'width' => 0,
                'length' => 0,
            );
        }
//        exit("FINAL");
        return array(
            'height' => $cubage['height'],
            'width' => $cubage['width'],
            'length' => $cubage['length'],
            'weight' => $data['weight']
        );
    }

    protected function get_max_values($height, $width, $length) {
        $find = array(
            'height' => max($height),
            'width' => max($width),
            'length' => max($length),
        );

        return $find;
    }

    protected function calculate_root($height, $width, $length, $max_values) {
        $cubage_total = $this->cubage_total($height, $width, $length);
        $root = 0;
        $biggest = max($max_values);

        if (0 !== $cubage_total && 0 < $biggest) {
            $division = $cubage_total / $biggest;
            $root = round(sqrt($division), 1);
        }
        return $root;
    }

    protected function cubage_total($height, $width, $length) {
        $total = 0;
        $total_items = count($height);

        for ($i = 0; $i < $total_items; $i++) {
            $total += $height[$i] * $width[$i] * $length[$i];
        }

        return $total;
    }

    public function filter_cep($cep = null) {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    public function get_code() {
        return $this->code;
    }

}
