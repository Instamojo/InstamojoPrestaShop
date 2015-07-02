<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/instamojo.php');
require_once(dirname(__FILE__) . '/instamojo-api.php');

$logger = new FileLogger(0); //0 == debug level, logDebug() won’t work without this.
$logger->setFilename(_PS_ROOT_DIR_ . "/log/imojo.log");

$instamojo = new instamojo();
$api_key = Configuration::get('INSTAMOJO_API_KEY');
$auth_token = Configuration::get('INSTAMOJO_AUTH_TOKEN');
$custom_field = Configuration::get('INSTAMOJO_CUSTOM_FIELD');
$logger->logDebug("API: $api_key| AUTH: $auth_token");
$api = new InstamojoAPI($api_key, $auth_token, 'https://www.instamojo.com/api/1.1/');

$success = true;
$total = 0;

try{
    $payment_id = $_GET["payment_id"];
    $logger->logDebug("Payment ID is: $payment_id");
    $response = $api->paymentDetail($payment_id);
    $logger->logDebug("Repsonse from Instamojo is: " . print_r($response, true));
    $logger->logDebug("Website's base url is " . _PS_BASE_URL_.__PS_BASE_URI__);
    if($response['status'] == "Credit"){
        $logger->logDebug("Status is 'Credit' for payment with id $payment_id");
        $cart_info = explode('-', $response['custom_fields'][$custom_field]['value']);
        $cart_id = $cart_info[0];
        $total = (float) $response["amount"];
    }
    else{
        // Dhoka diya re
        $logger->logDebug("Status is not credit for payment with id $payment_id");
        $success = false;
    }
}
catch (Exception $e){
    $logger->logDebug("An exception occurred while validating the payment " . $e);
    Tools::redirectLink(_PS_BASE_URL_.__PS_BASE_URI__ . 'index.php?controller=history');
}

try{
    if($success === true) {
        $cart = new Cart($cart_id); 
        $customer = new Customer((int)$cart->id_customer); 
        $extra_vars = array('transaction_id' => $response['payment_id']);
        $instamojo->validateOrder((int)$cart->id, _PS_OS_PAYMENT_, $total, $instamojo->displayName, NULL, $extra_vars, NULL, false, $customer->secure_key, NULL);
    }
    else {
    $instamojo->validateOrder($cart_id, _PS_OS_ERROR_, $total, $instamojo->displayName, NULL, array(), NULL, false, NULL);
    }
}
catch(Exception $e){
    $logger->logDebug("Something went wrong while validating the order with PrestaShop: " . $e);
}
try{
    $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int) $cart_id);
    $logger->logDebug("Data fetched from db successfully: " . print_r($result, true));
    Tools::redirectLink(_PS_BASE_URL_.__PS_BASE_URI__ . 'index.php?controller=order-detail&id_order=' . $result['id_order']);
}
catch(Exception $e){
    $logger->logDebug("Something went wrong while querying for the order id : " . $e);
    Tools::redirectLink(_PS_BASE_URL_.__PS_BASE_URI__ . 'index.php?controller=history');
}

?>
