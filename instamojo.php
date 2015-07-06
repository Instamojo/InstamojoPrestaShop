<?php
if (!defined('_PS_VERSION_'))
    exit;

class Instamojo extends PaymentModule {

public function __construct()
  {
    $this->name = 'instamojo';  // The value MUST be the name of the module's folder.
    $this->tab = 'payments_gateways';
    $this->version = '0.0.1';
    $this->author = 'Instamojo';
    $this->need_instance = 0;
    $this->currencies = true;
    $this->currencies_mode = 'radio';
    $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
    $this->bootstrap = true;
 
    parent::__construct();

    $this->page = basename(__FILE__, '.php'); 
    $this->displayName = $this->l('Instamojo');
    $this->description = $this->l('Accept Payments using Instamojo');
 
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
    if (!Configuration::get('INSTAMOJO'))      
      $this->warning = $this->l('No name provided');
  }

public function install()
{

if (!parent::install() || 
    !$this->registerHook('payment') ||
    !$this->registerHook('paymentReturn') ||
    !Configuration::updateValue('INSTAMOJO', 'Instant payments')
    )
    return false;
 
    return true;
}

public function uninstall()
{
  if (!parent::uninstall() ||
    !Configuration::deleteByName('INSTAMOJO') ||
    !Configuration::deleteByName('INSTAMOJO_API_KEY') ||
    !Configuration::deleteByName('INSTAMOJO_AUTH_TOKEN') ||
    !Configuration::deleteByName('INSTAMOJO_PRIVATE_SALT') ||
    !Configuration::deleteByName('INSTAMOJO_PAYMENT_LINK') ||
    !Configuration::deleteByName('INSTAMOJO_CUSTOM_FIELD')
  )
    return false;
 
  return true;
}

public function getContent()
{
    $output = null;
    $all_fine = 0;
 
    if (Tools::isSubmit('submit'.$this->name))
    {
        
        $checkout_label = strval(Tools::getValue('INSTAMOJO_CHECKOUT_BUTTON_LABEL'));
        $api_key = strval(Tools::getValue('INSTAMOJO_API_KEY'));
        $auth_token = strval(Tools::getValue('INSTAMOJO_AUTH_TOKEN'));
        $private_salt = strval(Tools::getValue('INSTAMOJO_PRIVATE_SALT'));
        $payment_link = strval(Tools::getValue('INSTAMOJO_PAYMENT_LINK'));
        $custom_field = strval(Tools::getValue('INSTAMOJO_CUSTOM_FIELD'));


        if (!$checkout_label
          || empty($checkout_label)){
            Configuration::updateValue('INSTAMOJO_CHECKOUT_BUTTON_LABEL', "Pay using Instamojo");
            $output .= $this->displayError($this->l('Invalid Configuration value for  Checkout button label. Default value of "Pay using Instamojo" is going to be used.'));
            
        }else{
            $all_fine += 1;
            Configuration::updateValue('INSTAMOJO_CHECKOUT_BUTTON_LABEL', $checkout_label);
        }
        if (!$api_key
          || empty($api_key)){
            $output .= $this->displayError($this->l('Invalid Configuration value for API key.'));
            
        }else{
            $all_fine += 1;
            Configuration::updateValue('INSTAMOJO_API_KEY', $api_key);
        }
        if (!$auth_token
          || empty($auth_token)){
            $output .= $this->displayError($this->l('Invalid Configuration value for Auth token.'));
        }
        else{
            $all_fine += 1;
            Configuration::updateValue('INSTAMOJO_AUTH_TOKEN', $auth_token);
        }
        if (!$private_salt
          || empty($private_salt)){
            $output .= $this->displayError($this->l('Invalid Configuration value for Private salt.'));
        }
        else{
            $all_fine += 1;
            Configuration::updateValue('INSTAMOJO_PRIVATE_SALT', $private_salt);
        }
        if (!$custom_field
          || empty($custom_field)){
            $output .= $this->displayError($this->l('Invalid Configuration value for Custom Field.'));
        }
        else{
            $all_fine += 1;
            Configuration::updateValue('INSTAMOJO_CUSTOM_FIELD', $custom_field);
        }
        if (!$payment_link
          || empty($payment_link)){
            $output .= $this->displayError($this->l('Invalid Configuration value for Payment Link.'));
        }
        else{
            Configuration::updateValue('INSTAMOJO_PAYMENT_LINK', base64_encode(html_entity_decode($payment_link)));
            $all_fine += 1;
        }
        if($all_fine === 6)
        {
            $output .= $this->displayConfirmation($this->l('All settings changed successfully.'));
        }
        else if($all_fine > 0 && $all_fine !== 6){
            $output .= $this->displayConfirmation($this->l('Some settings changed successfully.'));
        }
    }
    return $output.$this->displayForm();
}


public function displayForm()
{
    // Get default language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Checkout button label'),
                'name' => 'INSTAMOJO_CHECKOUT_BUTTON_LABEL',
                'size' => 32,
                'required' => false
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API Key'),
                'name' => 'INSTAMOJO_API_KEY',
                'size' => 32,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Auth token'),
                'name' => 'INSTAMOJO_AUTH_TOKEN',
                'size' => 32,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Private salt'),
                'name' => 'INSTAMOJO_PRIVATE_SALT',
                'size' => 32,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Payment Link'),
                'name' => 'INSTAMOJO_PAYMENT_LINK',
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Custom Field'),
                'name' => 'INSTAMOJO_CUSTOM_FIELD',
                'required' => true
            )
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'button'
        )
    );
     
    $helper = new HelperForm();
     
    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
     
    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );

    $helper->fields_value['INSTAMOJO_CHECKOUT_BUTTON_LABEL'] = Configuration::get('INSTAMOJO_CHECKOUT_BUTTON_LABEL'); 
    $helper->fields_value['INSTAMOJO_API_KEY'] = Configuration::get('INSTAMOJO_API_KEY');
    $helper->fields_value['INSTAMOJO_AUTH_TOKEN'] = Configuration::get('INSTAMOJO_AUTH_TOKEN');
    $helper->fields_value['INSTAMOJO_PRIVATE_SALT'] = Configuration::get('INSTAMOJO_PRIVATE_SALT');
    $helper->fields_value['INSTAMOJO_PAYMENT_LINK'] = base64_decode(Configuration::get('INSTAMOJO_PAYMENT_LINK'));
    $helper->fields_value['INSTAMOJO_CUSTOM_FIELD'] = Configuration::get('INSTAMOJO_CUSTOM_FIELD');

    return $helper->generateForm($fields_form);
}


public function hookdisplayPayment($params) {

        $logger = new FileLogger(0); //0 == debug level, logDebug() won’t work without this.
        $logger->setFilename(_PS_ROOT_DIR_ . "/log/imojo.log");

        if (!$this->active)
            return;
        //!$cart->OrderExists();

        $logger->logDebug("Hook Display Payment starts.");

        $customer = new Customer($params['cart']->id_customer);
        $email_address = $customer->email;
        $currency = trim($this->getCurrency()->iso_code);

        $amount = $params['cart']->getOrderTotal(true, 3);
        $cartId = $params['cart']->id;
        
        $address = new Address($params['cart']->id_address_invoice);

        $products = $params['cart']->getProducts();
        $quantity = '';
        $product_name = '';
        $product_count = count($products);
        for ($i = 0; $i < $product_count; $i++) {
            $quantity .= $products[$i]['cart_quantity'] . ', ';
            $product_name .= $products[$i]['name'] . ', ';
        }

        $product_name = (Tools::strlen($product_name) > 100) ? Tools::substr($product_name, 0, 100) : $product_name;
        $complete_address = $address->address1 . ' ' . $address->address2;
        $complete_address = (Tools::strlen($complete_address) > 100) ? Tools::substr($complete_address, 0, 100) : $complete_address;
        $logger->logDebug("$email_address | $currency | $amount | $cartId | $product_count | $product_name");
        // $module_version = (Tools::strlen($module_version) > 20) ? Tools::substr($module_version, 0, 20) : $module_version;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' && $_SERVER['HTTPS'] != 'OFF') {
            //TODO:: callback url, validate
            $redirect_url = 'https://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/instamojo/validation.php';
        } else {
            $redirect_url = 'http://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/instamojo/validation.php';
        }
        $imname = substr(trim($address->firstname . ' ' . $address->lastname), 0, 20);
        $imemail = substr($email_address, 0, 75);
        $imphone = substr(ltrim($address->phone_mobile, '+'), 0, 20);
        $imamount = $amount;
        $imtid = $cartId . '-' . date('his');

        $logger->logDebug("$imname | $imemail | $imphone | $imamount | $imtid");
        
        $checkout_label = Configuration::get('INSTAMOJO_CHECKOUT_BUTTON_LABEL');
        $checkout_label = $checkout_label ? $checkout_label : "Pay using Instamojo";
        $api_key = Configuration::get('INSTAMOJO_API_KEY');
        $auth_token = Configuration::get('INSTAMOJO_AUTH_TOKEN');
        $private_salt = Configuration::get('INSTAMOJO_PRIVATE_SALT');
        $payment_link = base64_decode(Configuration::get('INSTAMOJO_PAYMENT_LINK'));
        $custom_field = Configuration::get('INSTAMOJO_CUSTOM_FIELD');

        $payment_link = $payment_link . "?embed=form&";
        $payment_link .= "data_readonly=data_name&data_readonly=data_email&data_readonly=data_amount&data_readonly=data_phone&data_readonly=data_%s"; // readonly fields
        $payment_link .= "&data_hidden=data_%s&data_%s=%s"; // hidden + their values
        $payment_link .= "&data_name=%s&data_email=%s&data_amount=%s&data_phone=%s&data_sign=%s"; // readonly field values

        $logger->logDebug("$checkout_label | $api_key | $auth_token | $private_salt | $payment_link | $custom_field");
        
        $data = Array();
        $data['data_name'] = $imname;
        $data['data_email'] = $imemail;
        $data['data_amount'] = $imamount;
        $data['data_phone'] = $imphone;
        $data["data_" . $custom_field] = $imtid;

        $ver = explode('.', phpversion());
        $major = (int) $ver[0];
        $minor = (int) $ver[1];
        if($major >= 5 and $minor >= 4){
            ksort($data, SORT_STRING | SORT_FLAG_CASE);
        }
        else{
            uksort($data, 'strcasecmp');
        }

        $str = hash_hmac("sha1", implode("|", $data), $private_salt);

        $logger->logDebug("Signature is: $str");  

        $payment_link = sprintf($payment_link, $custom_field, $custom_field, $custom_field, $imtid, $imname, $imemail, $imamount, $imphone, $str);
        $logger->logDebug("Payment link is: $payment_link");
        $this->smarty->assign('payment_link', $payment_link);
        $this->smarty->assign('checkout_label', $checkout_label);

        return $this->display(__FILE__, '/views/templates/front/instamojo.tpl');
    }


public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $response_message = null, $extra_vars = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null) {
        if (!$this->active)
            return;

        parent::validateOrder((int) $id_cart, (int) $id_order_state, (float) $amount_paid, $payment_method, $response_message, $extra_vars, $currency_special, $dont_touch_amount, $secure_key, $shop);
    }

}

?>
