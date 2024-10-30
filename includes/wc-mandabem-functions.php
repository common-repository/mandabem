<?php

//ini_set('display_errors',true);
//error_reporting(E_ALL);

function update_rastreio($data) {
    
    $id = $_POST['id'];
    $rastreio = $_POST['rastreio'];
    $order = wc_get_order( $id ); 
    $order_id = key($order->get_items( 'shipping' ));
    $atualiza = wc_update_order_item_meta( $order_id, '_delivery_mandabem_rastreio', $rastreio);
    $status_cliente   = apply_filters('woocommerce_mandabem_status_post_apos_rastreio', 'nao_alterar'); 

    if($status_cliente != 'nao_alterar'){
        do_action ('woocommerce_order_edit_status', $id, $status_cliente);
        $order-> update_status ($status_cliente);
    }
}

function update_entrega($data) {
    
    $id = $_POST['id'];
    $entrega = $_POST['entrega'];
    $order = wc_get_order( $id ); 
    $order_id = key($order->get_items( 'shipping' ));
    $atualiza = wc_update_order_item_meta( $order_id, '_delivery_mandabem_entrega', $entrega);
    $status_cliente   = apply_filters('woocommerce_mandabem_status_post_apos_entrega', 'nao_alterar'); 

    if($status_cliente != 'nao_alterar'){
        do_action ('woocommerce_order_edit_status', $id, $status_cliente);
        $order-> update_status ($status_cliente);
    }
}

add_action( 'rest_api_init', function () {
  register_rest_route('mandabem', '/update_rastreio', array(
    'methods' => 'POST',
    'callback' => 'update_rastreio',
    'permission_callback' => '__return_true',
  ) );
} );

add_action( 'rest_api_init', function () {
  register_rest_route('mandabem', '/update_entrega', array(
    'methods' => 'POST',
    'callback' => 'update_entrega',
    'permission_callback' => '__return_true',
  ) );
} );
 

 
?>