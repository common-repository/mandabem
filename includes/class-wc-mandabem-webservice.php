<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Mandabem_Webservice {

    /**
     * Plugin Version.
     *
     * @var string
     */
    protected $version = 'v1.0';

    /**
     * Base URL to Mandabem.
     *
     * @var string
     */
    protected $base_url = "https://mandabem.com.br/ws/";

    /**
     * Shipping method ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Shipping zone instance ID.
     *
     * @var int
     */
    protected $instance_id;

    /**
     * Logger.
     *
     * @var WC_Logger
     */
    protected $log = null;

    /**
     * Store Id.
     *
     * @var string
     */
    protected $store_id = '';

    /**
     * Store Token.
     *
     * @var string
     */
    protected $store_token = '';

    /**
     * Debug mode.
     *
     * @var string
     */
    protected $debug = 'yes';

    public function get_store_id() {
        return $this->store_id;
    }

    public function get_store_token() {
        return $this->store_token;
    }

    public function set_store_id($id) {
        $this->store_id = $id;
    }

    public function set_store_token($token) {
        $this->store_token = $token;
    }

    public function set_debug($debug = 'no') {
        $this->debug = $debug;
    }

    public function __construct($id = 'mandabem', $instance_id = 0) {
        $this->id = $id;
        $this->instance_id = $instance_id;
        $this->log = new WC_Logger();
    }

    public function send_post($data_to_send = array()) {

        $url = $this->base_url . $data_to_send['endpoint'];

        if ($this->debug === 'yes') {
            $this->log->add($this->id, 'Requesting Mandabem WebServices: ' . $url);
        }
        
        $data_to_send['plataforma_id'] = $this->get_store_id();
        $data_to_send['plataforma_chave'] = $this->get_store_token();
        $data_to_send['integracao'] = 'wordpress';

        $shipping = null;
        $options = array('timeout' => 60, 'body' => $data_to_send, 'httpversion' => '1.1', 'user-agent' => 'Woocomerce Mandabem Plugin ' . $this->version);
        $response = wp_safe_remote_post($url, $options);

        if (is_wp_error($response)) {
            if ('yes' === $this->debug) {
                $this->log->add($this->id, 'WP_Error: ' . $response->get_error_message());
            }
        } elseif ($response['response']['code'] == 200) {
            $shipping = json_decode($response['body']);

            if (!$shipping) {
                if ($this->debug === 'yes') {
                    $this->log->add($this->id, "ERROR DECODE, JSON Mandabem response:\n" . print_r($response, true));
                }
            }
            
            if ($this->debug === 'yes') {
                $this->log->add($this->id, 'Response Webservice Mandabem: ' . print_r($response, true));
            }
            
        } else {
            if ($this->debug === 'yes') {
                $this->log->add($this->id, 'Erro ao acessar Webservice Mandabem: ' . print_r($response, true));
            }
        }

        return $shipping;
    }

}
