<?php

/*
Plugin Name: Mandabem
Plugin URI: http://wordpress.org/plugins/mandabem/
Description: Plugin para geração de valores de Frete e Postagem (etiqueta) na Plataforma Mandabem.
Author: Mandabem
Version: 2.0
Author URI: https://mandabem.com.br
*/

if (!defined('ABSPATH')) {
    exit;
}

define('WC_MANDABEM_VERSION', '2.0');
define('WC_MANDABEM_PATH', dirname(__FILE__));

if (!class_exists('WC_Mandabem')) {
    include_once dirname(__FILE__) . '/includes/class-wc-mandabem.php';

    add_action('plugins_loaded', array('WC_Mandabem', 'start_plugin'));
}

