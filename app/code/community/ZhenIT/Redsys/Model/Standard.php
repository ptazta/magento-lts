<?php

/**
 * Redsys  Checkout Module
 */
class ZhenIT_Redsys_Model_Standard extends Mage_Payment_Model_Method_Abstract
	/**
	 * @todo implements Mage_Payment_Model_Recurring_Profile_MethodInterface
	 */
{
	const PAYMENT_TYPE_AUTH    = 'AUTHORIZATION';
	const PAYMENT_TYPE_SALE    = 'SALE';
	const STATUS_PAID          = 'redsys_authorised';
	const STATUS_PREAUTHORISED = 'redsys_preauthorised';
	const STATUS_AUTHENTICATED = 'redsys_authenticated';
	const STATUS_REJECTED      = 'redsys_rejected';
	const STATUS_REFUNDED      = 'redsys_refunded';

	/**
	 * Availability options
	 */
	protected $_isGateway               = true;
	protected $_canAuthorize            = false;
	protected $_canCapture              = true;
	protected $_canCapturePartial       = true;
	protected $_canRefund               = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = false;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = true;
	protected $_redsysData  = null;

	protected $_code              = 'redsys_standard';
	protected $_formBlockType     = 'redsys/standard_form';
	protected $_allowCurrencyCode = array(
		'ADP', 'AED', 'AFA', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZM',
		'BAM', 'BBD', 'BDT', 'BGL', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV',
		'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP',
		'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CYP', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD',
		'ECS', 'ECV', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
		'GHC', 'GIP', 'GMD', 'GNF', 'GTQ', 'GWP', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG',
		'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES',
		'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR',
		'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGF', 'MKD', 'MMK', 'MNT',
		'MOP', 'MRO', 'MTL', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZM', 'NAD',
		'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR',
		'PLN', 'PYG', 'QAR', 'ROL', 'RUB', 'RUR', 'RWF', 'SAR', 'SBD', 'SCR', 'SDD',
		'SEK', 'SGD', 'SHP', 'SIT', 'SKK', 'SLL', 'SOS', 'SRG', 'STD', 'SVC', 'SYP',
		'SZL', 'THB', 'TJS', 'TMM', 'TND', 'TOP', 'TPE', 'TRL', 'TRY', 'TTD', 'TWD',
		'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEB', 'VND', 'VUV', 'XAF', 'XCD',
		'XOF', 'XPF', 'YER', 'YUM', 'ZAR', 'ZMK', 'ZWD'
	);

	/**
	 * Get Redsys session namespace
	 *
	 * @return ZhenIT_Redsys_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('redsys/session');
	}

	/**
	 * Get checkout session namespace
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckout() {
		return Mage::getSingleton('checkout/session');
	}

	/**
	 * Get current quote
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	public function getQuote() {
		return $this->getCheckout()->getQuote();
	}

	/**
	 * Using internal pages for input payment data
	 *
	 * @return bool
	 */
	public function canUseInternal() {
		return false;
	}

	public function canRefund() {
		if ('0' == $this->getConfigData('transactype'))
			return $this->_canRefund;
		if (!$transaction = $this->getInfoInstance()->getTransaction())
			return false;
		$params = $transaction->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
		/*No se puede reembolsa una confirmación de autorización*/
		if (in_array($params['Ds_TransactionType'], array('2', '8')))
			return false;

		return $this->_canRefund;
	}

	public function canCapture() {
		if (!$transaction = $this->getInfoInstance()->getAuthorizationTransaction())
			return false;
		$params = $transaction->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
		/*No se puede confirmar más de una vez una autorización*/
		if (!isset($params['Ds_TransactionType']) || in_array($params['Ds_TransactionType'], array('0', '2', '8')))
			return false;

		return $this->_canCapture;
	}

	/**
	 * Using for multiple shipping address
	 *
	 * @return bool
	 */
	public function canUseForMultishipping() {
		return false;
	}

	public function createFormBlock($name) {
		$block = $this->getLayout()->createBlock('redsys/standard_form', $name)
			->setMethod('redsys_standard')
			->setPayment($this->getPayment())
			->setTemplate('redsys/form.phtml');

		return $block;
	}

	public function getSuccessStatus() {
		switch ($this->getConfigData('transactype')) {
			case '0':
				return self::STATUS_PAID;
			case '1':
				return self::STATUS_PREAUTHORISED;
			case '7':
				return self::STATUS_AUTHENTICATED;
		}
	}

	/**
	 * Valida si el codigo de la moneda esta disponible
	 */
	public function validate() {
		parent::validate();
		$currency_code = $this->getQuote()->getBaseCurrencyCode();
		if (!in_array($currency_code, $this->_allowCurrencyCode)) {
			Mage::throwException(Mage::helper('redsys')->__('El codigo de moneda seleccionado (%s) no es compatible con Redsys', $currency_code));
		}
		return $this;
	}

	public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment) {
		return $this;
	}

	public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment) {

	}

	public function capture(Varien_Object $payment, $amount) {
		if ('0' == $this->getConfigData('transactype'))
			return $this;
		parent::capture($payment, $amount);
		$order = $payment->getOrder();
		Mage::getSingleton('redsys/client')->confirmar_preautorizacion($amount, $order);
		/*		$order->getPayment()
					->getAuthorizationTransaction()
					->setIsClosed(1)
					->save();*/
		return $this;
	}

	public function refund(Varien_Object $payment, $amount) {
		parent::refund($payment, $amount);
		$order = $payment->getOrder();
		Mage::getModel('redsys/client')->reembolso($amount, $order);
		//$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
		return $this;
	}

	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('redsys/standard/redirect');
	}

	/**
	 * Convierte la moneda de magento al codigo de Redsys
	 */
	public function convertToRedsysCurrency($cur) {
		$monedas = array(
			'ADP' => '020', 'AED' => '784', 'AFA' => '004', 'ALL' => '008',
			'AMD' => '051', 'ANG' => '532', 'AOA' => '973', 'ARS' => '032',
			'AUD' => '036', 'AWG' => '533', 'AZM' => '031', 'BAM' => '977',
			'BBD' => '052', 'BDT' => '050', 'BGL' => '100', 'BGN' => '975',
			'BHD' => '048', 'BIF' => '108', 'BMD' => '060', 'BND' => '096',
			'BOB' => '068', 'BOV' => '984', 'BRL' => '986', 'BSD' => '044',
			'BTN' => '064', 'BWP' => '072', 'BYR' => '974', 'BZD' => '084',
			'CAD' => '124', 'CDF' => '976', 'CHF' => '756', 'CLF' => '990',
			'CLP' => '152', 'CNY' => '156', 'COP' => '170', 'CRC' => '188',
			'CUP' => '192', 'CVE' => '132', 'CYP' => '196', 'CZK' => '203',
			'DJF' => '262', 'DKK' => '208', 'DOP' => '214', 'DZD' => '012',
			'ECS' => '218', 'ECV' => '983', 'EEK' => '233', 'EGP' => '818',
			'ERN' => '232', 'ETB' => '230', 'EUR' => '978', 'FJD' => '242',
			'FKP' => '238', 'GBP' => '826', 'GEL' => '981', 'GHC' => '288',
			'GIP' => '292', 'GMD' => '270', 'GNF' => '324', 'GTQ' => '320',
			'GWP' => '624', 'GYD' => '328', 'HKD' => '344', 'HNL' => '340',
			'HRK' => '191', 'HTG' => '332', 'HUF' => '348', 'IDR' => '360',
			'ILS' => '376', 'INR' => '356', 'IQD' => '368', 'IRR' => '364',
			'ISK' => '352', 'JMD' => '388', 'JOD' => '400', 'JPY' => '392',
			'KES' => '404', 'KGS' => '417', 'KHR' => '116', 'KMF' => '174',
			'KPW' => '408', 'KRW' => '410', 'KWD' => '414', 'KYD' => '136',
			'KZT' => '398', 'LAK' => '418', 'LBP' => '422', 'LKR' => '144',
			'LRD' => '430', 'LSL' => '426', 'LTL' => '440', 'LVL' => '428',
			'LYD' => '434', 'MAD' => '504', 'MDL' => '498', 'MGF' => '450',
			'MKD' => '807', 'MMK' => '104', 'MNT' => '496', 'MOP' => '446',
			'MRO' => '478', 'MTL' => '470', 'MUR' => '480', 'MVR' => '462',
			'MWK' => '454', 'MXN' => '484', 'MXV' => '979', 'MYR' => '458',
			'MZM' => '508', 'NAD' => '516', 'NGN' => '566', 'NIO' => '558',
			'NOK' => '578', 'NPR' => '524', 'NZD' => '554', 'OMR' => '512',
			'PAB' => '590', 'PEN' => '604', 'PGK' => '598', 'PHP' => '608',
			'PKR' => '586', 'PLN' => '985', 'PYG' => '600', 'QAR' => '634',
			'ROL' => '642', 'RUB' => '643', 'RUR' => '810', 'RWF' => '646',
			'SAR' => '682', 'SBD' => '090', 'SCR' => '690', 'SDD' => '736',
			'SEK' => '752', 'SGD' => '702', 'SHP' => '654', 'SIT' => '705',
			'SKK' => '703', 'SLL' => '694', 'SOS' => '706', 'SRG' => '740',
			'STD' => '678', 'SVC' => '222', 'SYP' => '760', 'SZL' => '748',
			'THB' => '764', 'TJS' => '972', 'TMM' => '795', 'TND' => '788',
			'TOP' => '776', 'TPE' => '626', 'TRL' => '792', 'TRY' => '949',
			'TTD' => '780', 'TWD' => '901', 'TZS' => '834', 'UAH' => '980',
			'UGX' => '800', 'USD' => '840', 'UYU' => '858', 'UZS' => '860',
			'VEB' => '862', 'VND' => '704', 'VUV' => '548', 'XAF' => '950',
			'XCD' => '951', 'XOF' => '952', 'XPF' => '953', 'YER' => '886',
			'YUM' => '891', 'ZAR' => '710', 'ZMK' => '894', 'ZWD' => '716',
		);
		if (isset($monedas[$cur])) {
			return $monedas[$cur];
		}
		return '';
	}

	/**
	 * El Valor 0, indicara que no se ha determinado el idioma del
	 * cliente (opcional). Otros valores posibles son:
	 * Castellano-001, Ingles-002, Catalan-003,
	 * Frances-004, Aleman-005, Portugues-009.
	 * 3 se considera su longitud maxima
	 */
	function calcLanguage($lan) {
		$langs = array(
			'es_ES' => '001',
			'en_US' => '002', 'en_GB' => '002', 'en_AU' => '002',
			'ca_ES' => '003',
			'fr_FR' => '004',
			'de_DE' => '005',
		);
		if (isset($langs[$lan])) {
			return $langs[$lan];
		}
		return '002';
	}

	public function getStandardCheckoutFormFields() {
		$a = $this->getQuote()->getShippingAddress();

		$order    = Mage::getModel('sales/order');
		$order_id = $this->getCheckout()->getLastOrderId();
		$order->load($order_id);

		$ord = $this->getCheckout()->getLastRealOrderId();

		$code         = $this->getConfigData('merchantnumber');
		$terminal     = $this->getConfigData('merchantterminal');
		$cc           = 'EUR';
		$currencyCode = $order->getOrderCurrencyCode();
		$tcs          = explode(',', $this->getConfigData('merchantterminal'));
		foreach ($tcs as $tc) {
			list($cc, $terminal) = explode(':', $tc);
			if ($currencyCode == $cc)
				break;
		}
		$currency = $this->convertToRedsysCurrency($cc);
		if ($order->getBaseCurrencyCode() == $cc) {
			$unconverted_ammount = $order->getBaseTotalDue();
		} else {
			// This uses the rate $currencyCode to $cc which might not be  1/($cc to $currencyCode)
			//$converted_ammount = Mage::helper('directory')->currencyConvert($order->getTotalDue(), $currencyCode, $cc);
			$rate                = 1 / (Mage::helper('directory')->currencyConvert(1, $cc, $currencyCode));
			$unconverted_ammount = $order->getTotalDue() * $rate;
		}

		$amount = round($unconverted_ammount * 100);
		if ($currency == '392')
			$amount = round($unconverted_ammount * 1000);
		$clave = $this->getClave();

		$merchurl    = Mage::getModel('core/url')->sessionUrlVar(Mage::getUrl('redsys/standard/callback'));
		$transactype = $this->getConfigData('transactype');
		if (!class_exists("RedsysAPI")) {
			$lib = Mage::getBaseDir('lib');
			require_once($lib . '/Redsys/RedsysAPI.php');
		}
		// Generamos la firma
		$redsys = new RedsysAPI;
		$redsys->setParameter("DS_MERCHANT_AMOUNT", $amount);
		$redsys->setParameter("DS_MERCHANT_ORDER", $ord);
		$redsys->setParameter("DS_MERCHANT_MERCHANTCODE", $this->getConfigData('merchantnumber'));
		$redsys->setParameter("DS_MERCHANT_CURRENCY", $currency);
		$redsys->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $transactype);
		$redsys->setParameter("DS_MERCHANT_TERMINAL", $terminal);
		$redsys->setParameter("DS_MERCHANT_MERCHANTURL", $merchurl);
		$redsys->setParameter("DS_MERCHANT_URLOK", Mage::getUrl('checkout/onepage/success'));
		$redsys->setParameter("DS_MERCHANT_URLKO", Mage::getUrl('redsys/standard/cancel'));
		$redsys->setParameter("Ds_Merchant_ConsumerLanguage", $this->calcLanguage(Mage::app()->getLocale()->getLocaleCode()));
		$redsys->setParameter("Ds_Merchant_ProductDescription", "Pago pedido: " . $order_id);
		$redsys->setParameter("Ds_Merchant_Titular", $a->getFirstname() . ' ' . $a->getLastname());
		$redsys->setParameter("Ds_Merchant_MerchantName", Mage::app()->getStore()->getName());
		$redsys->setParameter("Ds_Merchant_MerchantData", $order_id);
		$redsys->setParameter("Ds_Merchant_PayMethods", $this->getConfigData('paymethod'));
		$redsys->setParameter("Ds_Merchant_Module", 'ZhenIT_Redsys-3.5.0');

		//Datos de configuración
		$version = "HMAC_SHA256_V1";

		$request      = "";
		$paramsBase64 = $redsys->createMerchantParameters();
		$signatureMac = $redsys->createMerchantSignature($this->getClave());

		$sArr = array(
			'Ds_SignatureVersion' => $version,
			'Ds_MerchantParameters' => $paramsBase64,
			'Ds_Signature' => $signatureMac
		);

		return $sArr;
	}

	/**
	 * Retorna la clave segun la configuracion
	 */
	public function getClave() {
		return $this->getConfigData('merchantpassword');
	}

	/**
	 * Retorna la url segun la configuracion
	 */
	public function getRedsysUrl() {
		if ($this->getConfigData('specificurl') != '') {
			return $this->getConfigData('specificurl');
		}

		if ($this->getConfigData('entorno') == '1') {
			/**
			 * Entorno Real
			 */
			return "https://sis.redsys.es/sis/realizarPago";
		} else {
			/**
			 * Entorno de pruebas
			 */
			return "https://sis-t.redsys.es:25443/sis/realizarPago";
		}
	}


	public function getRedsysData($params){
		if(is_null($this->_redsysData)){
			/** Recoger datos de respuesta **/
			$version      = $params["Ds_SignatureVersion"];
			if (!class_exists("RedsysAPI")) {
				$lib = Mage::getBaseDir('lib');
				require_once($lib . '/Redsys/RedsysAPI.php');
			}
			// Se crea Objeto
			$this->_redsysData = new RedsysAPI;
			$decoded = $this->_redsysData->decodeMerchantParameters($params["Ds_MerchantParameters"]);
			$this->_redsysData->stringToArray($decoded);
		}

		return $this->_redsysData;
	}

	public function firmaValida($params) {
		$firma_remota = $params["Ds_Signature"];
		$redsys = $this->getRedsysData($params);

		/** Clave **/
		$kc = $this->getClave();

		/** Se calcula la firma **/
		$firma_local = $this->getRedsysData($params)->createMerchantSignatureNotif($kc, $params["Ds_MerchantParameters"]);

		/** Extraer datos de la notificación **/
		$total     = $redsys->getParameter('Ds_Amount');
		$pedido    = $redsys->getParameter('Ds_Order');
		$codigo    = $redsys->getParameter('Ds_MerchantCode');
		$moneda    = $redsys->getParameter('Ds_Currency');
		$respuesta = $redsys->getParameter('Ds_Response');

		if ($firma_local === $firma_remota
			&& RedsysHelper::checkRespuesta($respuesta)
			&& RedsysHelper::checkMoneda($moneda)
			&& RedsysHelper::checkFuc($codigo)
			&& RedsysHelper::checkPedidoNum($pedido)
			&& RedsysHelper::checkImporte($total)
		)
			// Formatear variables
			return intval($respuesta);

		return -1;
	}

	/**
	 * Pagos recurrentes
	 * @todo En cuanto funcione bien en magento programar los metodos para
	 * pagos recurrentes
	 */
	/**
	 * public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
	 * {
	 * }
	 *
	 * public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo)
	 * {
	 * }
	 *
	 * public function getRecurringProfileDetails($referenceId, Varien_Object $result)
	 * {
	 * }
	 *
	 * public function canGetRecurringProfileDetails()
	 * {
	 * }
	 *
	 * public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
	 * {
	 * }
	 *
	 * public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile)
	 * {
	 * }
	 *
	 */
}
