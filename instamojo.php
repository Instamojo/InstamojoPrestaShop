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
    !Configuration::deleteByName('INSTAMOJO_PAYMENT_BUTTON_HTML') ||
    !Configuration::deleteByName('INSTAMOJO_CUSTOM_FIELD')
  )
    return false;
 
  return true;
}

public function getContent()
{
    $output = null;
    $all_fine = 1;
 
    if (Tools::isSubmit('submit'.$this->name))
    {
        $api_key = strval(Tools::getValue('INSTAMOJO_API_KEY'));
        $auth_token = strval(Tools::getValue('INSTAMOJO_AUTH_TOKEN'));
        $private_salt = strval(Tools::getValue('INSTAMOJO_PRIVATE_SALT'));
        $payment_button_html = strval(Tools::getValue('INSTAMOJO_PAYMENT_BUTTON_HTML'));
        $custom_field = strval(Tools::getValue('INSTAMOJO_CUSTOM_FIELD'));


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
        if (!$payment_button_html
          || empty($payment_button_html)){
            $output .= $this->displayError($this->l('Invalid Configuration value for Payment button HTML.'));
        }
        else{
            $doc = new DOMDocument();
            $doc->loadHTML($payment_button_html);
            $nodes = $doc->getElementsByTagName('a');
            foreach($nodes as $node){
                $payment_link = $node->getAttribute('href');
                break;
            }
 
            $link = $payment_link . "?embed=form&";
            $link .= "data_readonly=data_name&data_readonly=data_email&data_readonly=data_amount&data_readonly=data_phone&data_readonly=data_%s"; // readonly fields
            $link .= "&data_hidden=data_%s&data_%s=%s"; // hidden + their values
            $link .= "&data_name=%s&data_email=%s&data_amount=%s&data_phone=%s&data_sign=%s"; // readonly field values
            $node->setAttribute('href', $link);
            $html = $doc->saveHTML();
            $output = Array(); 
            preg_match("/<html><body>(.*?)<\/body><\/html>/", $html, $output);
            Configuration::updateValue('INSTAMOJO_PAYMENT_BUTTON_HTML', base64_encode(html_entity_decode($payment_button_html)));
            Configuration::updateValue('INSTAMOJO_PAYMENT_BUTTON_ACTUAL_HTML', html_entity_decode(base64_encode($output[1])));
            $all_fine += 1;
        }
        if($all_fine === 5)
        {
            $output .= $this->displayConfirmation($this->l('All settings changed successfully.'));
        }
        else if($all_fine > 0 && $all_fine !== 4){
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
                'label' => $this->l('Payment button HTML'),
                'name' => 'INSTAMOJO_PAYMENT_BUTTON_HTML',
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
     
    $helper->fields_value['INSTAMOJO_API_KEY'] = Configuration::get('INSTAMOJO_API_KEY');
    $helper->fields_value['INSTAMOJO_AUTH_TOKEN'] = Configuration::get('INSTAMOJO_AUTH_TOKEN');
    $helper->fields_value['INSTAMOJO_PRIVATE_SALT'] = Configuration::get('INSTAMOJO_PRIVATE_SALT');
    $helper->fields_value['INSTAMOJO_PAYMENT_BUTTON_HTML'] = base64_decode(Configuration::get('INSTAMOJO_PAYMENT_BUTTON_HTML'));
    $helper->fields_value['INSTAMOJO_CUSTOM_FIELD'] = Configuration::get('INSTAMOJO_CUSTOM_FIELD');

    return $helper->generateForm($fields_form);
}


public function hookdisplayPayment($params) {
        
        if (!$this->active)
            return;
        //!$cart->OrderExists();
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

        $api_key = Configuration::get('INSTAMOJO_API_KEY');
        $auth_token = Configuration::get('INSTAMOJO_AUTH_TOKEN');
        $private_salt = Configuration::get('INSTAMOJO_PRIVATE_SALT');
        $payment_button_html = base64_decode(Configuration::get('INSTAMOJO_PAYMENT_BUTTON_ACTUAL_HTML'));
        $custom_field = Configuration::get('INSTAMOJO_CUSTOM_FIELD');
        
        $data = Array();
        $data['data_name'] = $imname;
        $data['data_email'] = $imemail;
        $data['data_amount'] = $imamount;
        $data['data_phone'] = $imphone;
        $data["data_" . $custom_field] = $imtid;
        ksort($data, SORT_STRING | SORT_FLAG_CASE);
        $str = hash_hmac("sha1", implode("|", $data), $private_salt);
        
        $payment_button_html = sprintf($payment_button_html, $custom_field, $custom_field, $custom_field, $imtid, $imname, $imemail, $imamount, $imphone, $str);
        $this->smarty->assign('payment_button_html', $payment_button_html);

        return $this->display(__FILE__, '/views/templates/front/instamojo.tpl');
    }


public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $response_message = null, $extra_vars = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null) {
        if (!$this->active)
            return;
        parent::validateOrder((int) $id_cart, (int) $id_order_state, (float) $amount_paid, $payment_method, $response_message, $extra_vars, $currency_special, true, false, null);
    }

}

?>
