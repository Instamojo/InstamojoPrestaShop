<?php
if (!defined('_PS_VERSION_'))
	exit;

class InstamojoPrestaShop extends PaymentModule
{
	private $error_messages;
	public function __construct()
	{
		$this->name = 'InstamojoPrestaShop';
		$this->tab = 'payments_gateways';
		$this->version = '2.0.4';
		$this->author = 'Instamojo';
		$this->need_instance = 0;
		$this->controllers = array('validation');
		$this->is_eu_compatible = 1;
		$this->error_messages;
		$this->bootstrap = true;
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
 
		parent::__construct();

		$this->displayName = $this->l('Instamojo');
		$this->description = $this->l('Accept Online payments');

		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE', 'PS_OS_PAYMENT', 'PS_OS_PREPARATION', 'PS_OS_SHIPPING', 'PS_OS_CANCELED', 'PS_OS_REFUND', 'PS_OS_ERROR', 'PS_OS_OUTOFSTOCK', 'PS_OS_BANKWIRE', 'PS_OS_PAYPAL', 'PS_OS_WS_PAYMENT');
		if (!Configuration::get('PS_OS_PAYMENT'))
			foreach ($updateConfig as $u)
				if (!Configuration::get($u) && defined('_'.$u.'_'))
					Configuration::updateValue($u, constant('_'.$u.'_'));
		
		/* Check if cURL is enabled */
		if (!is_callable('curl_exec'))
			$this->warning = $this->l('cURL extension must be enabled on your server to use this module.');
		
	}

	public function install()
	{
		parent::install();
		$this->registerHook('payment');
		$this->registerHook('displayPaymentEU');
		$this->registerHook('paymentReturn');
		Configuration::updateValue('instamojo_checkout_label', 'Pay Using Instamojo');
		return true;
	}
	
	
	public function uninstall()
	{
		  parent::uninstall();
		  Configuration::deleteByName('instamojo_client_id');
		  Configuration::deleteByName('instamojo_client_secret');
		  Configuration::deleteByName('instmaojo_testmode');
		  Configuration::deleteByName('instamojo_checkout_label');
		return true;
	}
	public function hookPayment()
	{
		if (!$this->active)
			return ;
		
		$this->smarty->assign(array(
			'this_path' => $this->_path, //keep for retro compat
			'this_path_instamojo' => $this->_path,
			'checkout_label' => $this->l((Configuration::get('instamojo_checkout_label')) ? Configuration::get('instamojo_checkout_label'): "Pay using Instamojo"),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/' . $this->name . '/'
		));
		
		return $this->display(__FILE__, 'payment.tpl');
	}
	
	public function hookDisplayPaymentEU()
	{
		if (!$this->active)
			return ;
		
		return array(
			'cta_text' => $this->l((Configuration::get('instamojo_checkout_label'))?Configuration::get('instamojo_checkout_label'):"Pay using Instamojo"),
			'logo' => Media::getMediaPath(dirname(__FILE__).'/instamojo.png'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array('confirm' => true), true)
		); 
		
	}
	
	
	public function getInstamojoObject($logger){
		include_once __DIR__. DIRECTORY_SEPARATOR . "lib/instamojo.php";
		$credentials = $this->getConfigValues();
		$logger->logDebug("Credintials Client ID: $credentials[instamojo_client_id] Client Secret : $credentials[instamojo_client_secret] TestMode : $credentials[instamojo_testmode] ");
		$api  = new InstamojoApi($credentials['instamojo_client_id'],$credentials['instamojo_client_secret'],$credentials['instamojo_testmode']);
		return $api;
	}
	
	public function hookPaymentReturn()
	{
		if (!$this->active)
			return ;
		return ;
	}
	
	public function getConfigValues(){
		
		$data = array();
		$data['instamojo_client_id'] = Configuration::get('instamojo_client_id');
		$data['instamojo_client_secret'] = Configuration::get('instamojo_client_secret');
		$data['instamojo_testmode'] = Configuration::get('instamojo_testmode');
		$data['instamojo_checkout_label'] = Configuration::get('instamojo_checkout_label');
		return $data;
	}
	
	
	public function validate_data(){
		$this->error_messages = "";

		if(!strval(Tools::getValue('instamojo_client_id')))
			$this->error_messages .= "Client ID is Required<br/>";
		 if(!strval(Tools::getValue('instamojo_client_secret')))
			$this->error_messages .= "Client Secret is Required<br/>";
		
		return !$this->error_messages;
	}
	
	# Show Configuration form in admin panel.
	public function getContent()
	{
		$output = null;
		// $order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);
		if (Tools::isSubmit('submit'.$this->name))
		{
			$data = array(); 
			$data['instamojo_client_id'] = strval(Tools::getValue('instamojo_client_id'));
			$data['instamojo_client_secret'] = strval(Tools::getValue('instamojo_client_secret'));
			$data['instamojo_testmode'] = strval(Tools::getValue('instamojo_testmode'));
			$data['instamojo_checkout_label'] = strval(Tools::getValue('instamojo_checkout_label'));
			if ($this->validate_data($data))
			{
				Configuration::updateValue('instamojo_client_id', $data['instamojo_client_id']);
				Configuration::updateValue('instamojo_client_secret', $data['instamojo_client_secret']);
				Configuration::updateValue('instamojo_testmode', $data['instamojo_testmode']);
				Configuration::updateValue('instamojo_checkout_label', $data['instamojo_checkout_label']);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}else
				$output .= $this->displayError($this->error_messages);
		}
		return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		 
		// Init Fields form array
		$fields_form =array();
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
					array(
					'type' => 'text',
					'label' => $this->l('Checkout Label'),
					'name' => 'instamojo_checkout_label',
			 		'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Client ID'),
					'name' => 'instamojo_client_id',
					'required' => true
				),
			
			
				array(
					'type' => 'text',
					'label' => $this->l('Client Secret'),
					'name' => 'instamojo_client_secret',
					'required' => true
				),
			
				array(
				  'type'      => 'radio',                      
				  'label'     => $this->l('Test Mode'),        
				  'name'      => 'instamojo_testmode',         
				  'required'  => true,                         
				  'is_bool'   => true,                         
				  'values'    => array(                        
					array(
					  'id'    => 'active_on',                  
					  'value' => 1,                               
					  'label' => $this->l('Enabled')           
					),
					array(
					  'id'    => 'active_off',
					  'value' => 0,
					  'label' => $this->l('Disabled')
					)
				  ),
				)
			),
			
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
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
		
		 
		// Load current value
		$helper->fields_value = $this->getConfigValues();
		 
		return $helper->generateForm($fields_form);
	}
}
