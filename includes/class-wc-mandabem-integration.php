<?php

//ini_set('display_errors',true);
//error_reporting(E_ALL);
if (!defined('ABSPATH')) {
    exit;
}

/**
 * mandabem integration class.
 */
class WC_Mandabem_Integration extends WC_Integration {

    /**
     * Initialize integration actions.
     */
    public function __construct() {
        $this->id = 'mandabem-integration';
        $this->method_title = __('Mandabem', 'woocommerce-mandabem');

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->store_id = $this->get_option('store_id');
        $this->store_token = $this->get_option('store_token');
        $this->status_post_package = $this->get_option('status_post_package');
        $this->status_post_envio_package = $this->get_option('status_post_envio_package');
        $this->status_post_apos_rastreio = $this->get_option('status_post_apos_rastreio');
        $this->status_post_apos_entrega = $this->get_option('status_post_apos_entrega');

        add_filter('woocommerce_mandabem_credentials_data', array($this, 'get_credentials'), 10);
//        if (is_admin()) {
        add_filter('woocommerce_mandabem_status_post_apos_rastreio', array($this, 'status_post_apos_rastreio'), 10);
        add_filter('woocommerce_mandabem_status_post_apos_entrega', array($this, 'status_post_apos_entrega'), 10);
        add_filter('woocommerce_mandabem_status_post_envio_package', array($this, 'status_post_envio_package'), 10);
        add_filter('woocommerce_mandabem_get_status_post_envio', array($this, 'get_status_post_envio'), 10);
//        }
        // Actions.
        add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
    }

    public function get_status_post_envio() {
        return $this->status_post_package ? $this->status_post_package : 'wc-completed';
    }

    public function status_post_envio_package() {
        return $this->status_post_envio_package ? $this->status_post_envio_package : 'SEDEX';
    }

    public function status_post_apos_rastreio() {
        return $this->status_post_apos_rastreio ? $this->status_post_apos_rastreio : 'nao_alterar';
    }
    public function status_post_apos_entrega() {
        return $this->status_post_apos_entrega ? $this->status_post_apos_entrega : 'nao_alterar';
    }

    public function get_credentials() {
        return array(
            'store_id' => $this->store_id,
            'store_token' => $this->store_token,
        );
    }

    public function init_form_fields() {


        $tipo_envio = ['SEDEX' => 'SEDEX', 'PAC' => 'PAC', 'PACMINI' => 'ENVIO MINI'];
        $status_cliente = array( 
            'nao_alterar' => 'Não alterar',
        );
        $statuses = wc_get_order_statuses();
        $this->form_fields = array(
            'store_id' => array(
                'title' => __('ID da Api Mandabem', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Seu ID cadastrado na Plataforma Mandabem', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '',
            ),
            'store_token' => array(
                'title' => __('Token da Api Mandabem', 'woocommerce-mandabem'),
                'type' => 'text',
                'description' => __('Seu Token cadastrado na Plataforma Mandabem.', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'default' => '',
            ),
            'status_post_package' => array(
                'title' => __('Status geração Envio', 'woocommerce-mandabem'),
                'type' => 'select',
                'default' => 'wc-completed',
                'class' => 'wc-enhanced-select',
                'description' => __('Status para a geração de envio Mandabem', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'options' => $statuses
            ),
            'status_post_envio_package' => array(
                'title' => __('Metodo Padrão para geração de Envio Frete Gratis', 'woocommerce-mandabem'),
                'type' => 'select',
                'default' => 'SEDEX',
                'class' => 'wc-enhanced-select',
                'description' => __('Ao escolher um dos metodos, sempre que selecionada a opção "FRETE GRATIS", usaremos essa opção como padrão', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'options' => $tipo_envio
            ),
            'status_post_apos_rastreio' => array(
                'title' => __('Status para alteração após envio do Rastreio', 'woocommerce-mandabem'),
                'type' => 'select',
                'default' => 'nao_alterar',
                'class' => 'wc-enhanced-select',
                'description' => __('Ao receber o número do rastreio, você pode escolher para qual status mudar seu pedido', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'options' => array_merge($statuses,$status_cliente)
            ),
            'status_post_apos_entrega' => array(
                'title' => __('Status para alteração após a entrega do Produto', 'woocommerce-mandabem'),
                'type' => 'select',
                'default' => 'nao_alterar',
                'class' => 'wc-enhanced-select',
                'description' => __('Ao receber a confirmação de entrega do produto, você pode escolher para qual status mudar seu pedido', 'woocommerce-mandabem'),
                'desc_tip' => true,
                'options' => array_merge($statuses,$status_cliente)
            )
        );

        if (isset($_SERVER['REQUEST_METHOD']) && COUNT($_POST) && isset($_POST['woocommerce_mandabem-integration_store_id']) ) {
            $this->send_data_domain();
        }
    }

    // @Override 
    public function admin_options() {
        echo '<h2>' . esc_html($this->get_method_title()) . '</h2>';
        echo '<div><input type="hidden" name="section" value="' . esc_attr($this->id) . '" /></div>';
        echo '<table class="form-table">' . $this->generate_settings_html($this->get_form_fields(), false) . '</table>';
    }

    protected function get_tracking_log_link() {
        return ' <a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs&log_file=correios-tracking-history-' . sanitize_file_name(wp_hash('mandabem-tracking-history')) . '.log')) . '">' . __('View logs.', 'woocommerce-mandabem') . '</a>';
    }

    public function send_data_domain() {

        $id = $this->get_option('store_id');
        $token = $this->get_option('store_token');
        $envio = $this->get_option('status_post_package');

        $user_data = apply_filters('woocommerce_mandabem_credentials_data', array(
            'store_id' =>$id,
            'store_token' => $token,
        ));

        $api = new WC_Mandabem_Webservice($this->id);
        $api->set_store_id($user_data['store_id']);
        $api->set_store_token($user_data['store_token']);

        $_SERVER['SERVER_PROTOCOL'];
        $protocolo = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false) ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $url = ''; //$_SERVER['REQUEST_URI'];
        $link = $protocolo . '://' . $host . $url;

        $data_send = array(
            'domain' => $link,
            'endpoint' => 'update_domain'
        );

        $data_send = array(
            'domain' => $link,
            'endpoint' => 'update_domain',
            'metodo_envio' => $envio
        );

        $response = $api->send_post($data_send);
    }

}
