<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/instamojo.php');
require_once(dirname(__FILE__) . '/instamojo-api.php');

$instamojo = new instamojo();
$api_key = Configuration::get('INSTAMOJO_API_KEY');
$auth_token = Configuration::get('INSTAMOJO_AUTH_TOKEN');
$custom_field = Configuration::get('INSTAMOJO_CUSTOM_FIELD');

$api = new InstamojoAPI($api_key, $auth_token, 'https://www.instamojo.com/api/1.1/');

$success = true;

try{
    $response = $api->paymentDetail($_GET["payment_id"]);
    print_r($response);
    if($response['status'] == "Credit"){
        $cart_info = explode('-', $response['custom_fields'][$custom_field]['value']);
        $cart_id = $cart_info[0];
        $total = (float) $response["amount"];

    }
    else{
        // Dhoka diya re
        $success = false;
    }
}
catch (Exception $e){
    $success = false;
}

if($success === true) {
$instamojo->validateOrder($cart_id, _PS_OS_PAYMENT_, $total, $instamojo->displayName, $response, $cart_id, null, false, null, null);
}
else {
$instamojo->validateOrder($cart_id, _PS_OS_ERROR_, $total, $instamojo->displayName, array(), $cart_id, null, false, null, null);
}

$result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int) $cart_id);
Tools::redirectLink(__PS_BASE_URI__ . 'order-detail.php?id_order=' . $result['id_order']);

?>