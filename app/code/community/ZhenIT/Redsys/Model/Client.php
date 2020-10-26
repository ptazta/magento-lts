<?php

/**
 * Created by PhpStorm.
 * User: mikel
 * Date: 4/8/14
 * Time: 11:25 AM
 */
class ZhenIT_Redsys_Model_Client extends Mage_Payment_Model_Method_Abstract
{

	const URL_INTE = 'https://sis-i.redsys.es:25443/sis/services/SerClsWSEntrada';
	const URL_TEST = 'https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada';
	const URL_REAL = 'https://sis.redsys.es/sis/services/SerClsWSEntrada';
	const ERROR_SIS0007 = "Error al desmontar el XML de entrada";
	const ERROR_SIS0008 = "Error falta Ds_Merchant_MerchantCode";
	const ERROR_SIS0009 = "Error de formato en Ds_Merchant_MerchantCode";
	const ERROR_SIS0010 = "Error falta Ds_Merchant_Terminal";
	const ERROR_SIS0011 = "Error de formato en Ds_Merchant_Terminal";
	const ERROR_SIS0014 = "Error de formato en Ds_Merchant_Order";
	const ERROR_SIS0015 = "Error falta Ds_Merchant_Currency";
	const ERROR_SIS0016 = "Error de formato en Ds_Merchant_Currency";
	const ERROR_SIS0018 = "Error falta Ds_Merchant_Amount";
	const ERROR_SIS0019 = "Error de formato en Ds_Merchant_Amount";
	const ERROR_SIS0020 = "Error falta Ds_Merchant_MerchantSignature";
	const ERROR_SIS0021 = "Error la Ds_Merchant_MerchantSignature viene vacía";
	const ERROR_SIS0022 = "Error de formato en Ds_Merchant_TransactionType";
	const ERROR_SIS0023 = "Error Ds_Merchant_TransactionType desconocido";
	const ERROR_SIS0026 = "Error No existe el comercio / terminal enviado";
	const ERROR_SIS0027 = "Error Moneda enviada por el comercio es diferente a la que tiene asignada para ese terminal";
	const ERROR_SIS0028 = "Error Comercio / terminal está dado de baja";
	const ERROR_SIS0031 = "Error en un pago con tarjeta ha llegado un tipo de operación no valido";
	const ERROR_SIS0030 = "Método de pago no definido";
	const ERROR_SIS0034 = "Error de acceso a la Base de Datos";
	const ERROR_SIS0038 = "Error en java";
	const ERROR_SIS0040 = "Error el comercio / terminal no tiene ningún método de pago asignado";
	const ERROR_SIS0041 = "Error en el cálculo de la firma de datos del comercio";
	const ERROR_SIS0042 = "La firma enviada no es correcta";
	const ERROR_SIS0046 = "El BIN de la tarjeta no está dado de alta";
	const ERROR_SIS0051 = "Error número de pedido repetido";
	const ERROR_SIS0054 = "Error no existe operación sobre la que realizar la devolución";
	const ERROR_SIS0055 = "Error no existe más de un pago con el mismo número de pedido";
	const ERROR_SIS0056 = "La operación sobre la que se desea devolver no está autorizada";
	const ERROR_SIS0057 = "El importe a devolver supera el permitido";
	const ERROR_SIS0058 = "Inconsistencia de datos, en la validación de una confirmación";
	const ERROR_SIS0059 = "Error no existe operación sobre la que realizar la devolución";
	const ERROR_SIS0060 = "Ya existe una confirmación asociada a la preautorización";
	const ERROR_SIS0061 = "La preautorización sobre la que se desea confirmar no está autorizada";
	const ERROR_SIS0062 = "El importe a confirmar supera el permitido";
	const ERROR_SIS0063 = "Error. Número de tarjeta no disponible";
	const ERROR_SIS0064 = "Error. El número de tarjeta no puede tener más de 19 posiciones";
	const ERROR_SIS0065 = "Error. El número de tarjeta no es numérico";
	const ERROR_SIS0066 = "Error. Mes de caducidad no disponible";
	const ERROR_SIS0067 = "Error. El mes de la caducidad no es numérico";
	const ERROR_SIS0068 = "Error. El mes de la caducidad no es válido";
	const ERROR_SIS0069 = "Error. Año de caducidad no disponible";
	const ERROR_SIS0070 = "Error. El Año de la caducidad no es numérico";
	const ERROR_SIS0071 = "Tarjeta caducada";
	const ERROR_SIS0072 = "Operación no anulable";
	const ERROR_SIS0074 = "Error falta Ds_Merchant_Order";
	const ERROR_SIS0075 = "Error el Ds_Merchant_Order tiene menos de 4 posiciones o más de 12";
	const ERROR_SIS0076 = "Error el Ds_Merchant_Order no tiene las cuatro primeras posiciones numéricas";
	const ERROR_SIS0078 = "Método de pago no disponible";
	const ERROR_SIS0079 = "Error al realizar el pago con tarjeta";
	const ERROR_SIS0081 = "La sesión es nueva, se han perdido los datos almacenados";
	const ERROR_SIS0089 = "El valor de Ds_Merchant_ExpiryDate no ocupa 4 posiciones";
	const ERROR_SIS0092 = "El valor de Ds_Merchant_ExpiryDate es nulo";
	const ERROR_SIS0093 = "Tarjeta no encontrada en la tabla de rangos";
	const ERROR_SIS0112 = "Error. El tipo de transacción especificado en Ds_Merchant_Transaction_Type no esta permitido";
	const ERROR_SIS0115 = "Error no existe operación sobre la que realizar el pago de la cuota";
	const ERROR_SIS0116 = "La operación sobre la que se desea pagar una cuota no es una operación válida";
	const ERROR_SIS0117 = "La operación sobre la que se desea pagar una cuota no está autorizada";
	const ERROR_SIS0118 = "Se ha excedido el importe total de las cuotas";
	const ERROR_SIS0119 = "Valor del campo Ds_Merchant_DateFrecuency no válido";
	const ERROR_SIS0120 = "Valor del campo Ds_Merchant_CargeExpiryDate no válido";
	const ERROR_SIS0121 = "Valor del campo Ds_Merchant_SumTotal no válido";
	const ERROR_SIS0122 = "Valor del campo Ds_merchant_DateFrecuency o Ds_Merchant_SumTotal tiene formato incorrecto";
	const ERROR_SIS0123 = "Se ha excedido la fecha tope para realizar transacciones";
	const ERROR_SIS0124 = "No ha transcurrido la frecuencia mínima en un pago recurrente sucesivo";
	const ERROR_SIS0132 = "La fecha de Confirmación de Autorización no puede superar en más de 7 días a la de Preautorización";
	const ERROR_SIS0139 = "Error el pago recurrente inicial está duplicado";
	const ERROR_SIS0142 = "Tiempo excedido para el pago";
	const ERROR_SIS0216 = "Error Ds_Merchant_CVV2 tiene mas de 3/4 posiciones";
	const ERROR_SIS0217 = "Error de formato en Ds_Merchant_CVV2";
	const ERROR_SIS0221 = "Error el CVV2 es obligatorio";
	const ERROR_SIS0222 = "Ya existe una anulación asociada a la preautorización";
	const ERROR_SIS0223 = "La preautorización que se desea anular no está autorizada";
	const ERROR_SIS0225 = "Error no existe operación sobre la que realizar la anulación";
	const ERROR_SIS0226 = "Inconsistencia de datos, en la validación de una anulación";
	const ERROR_SIS0227 = "Valor del campo Ds_Merchan_TransactionDate no válido";
	const ERROR_SIS0252 = "El comercio no permite el envío de tarjeta";
	const ERROR_SIS0253 = "La tarjeta no cumple el check-digit";
	const ERROR_SIS0261 = "Operación detenida por superar el control de restricciones en la entrada al SIS";
	const ERROR_SIS0274 = "Tipo de operación desconocida o no permitida por esta entrada al SIS";
	const ERROR_SIS0298 = "El comercio no permite realizar operaciones de Tarjeta en Archivo";
	const ERROR_SIS0319 = "El comercio no pertenece al grupo especificado en Ds_Merchant_Group";
	const ERROR_SIS0321 = "La referencia indicada en Ds_Merchant_Identifier no está asociada al";
	const ERROR_SIS0322 = "Error de formato en Ds_Merchant_Group";
	const ERROR_SIS0325 = "Se ha pedido no mostrar pantallas pero no se ha enviado ninguna referencia de tarjeta";

	var $currency = null;
	var $merchant = null;
	var $terminal = null;
	var $key = null;
	var $file = '/tmp/http_client.log';
	var $debug = true;
	var $url = null;

	public function init($order)
	{
		$model = Mage::getSingleton('redsys/standard');
		$this->merchant = $model->getConfigData('merchantnumber');
		$currencyCode = $order->getOrderCurrencyCode();
		$tcs = explode(',', $model->getConfigData('merchantterminal'));
		foreach ($tcs as $tc) {
			list($cc, $this->terminal) = explode(':', $tc);
			if ($currencyCode == $cc)
				break;
		}
		$this->currency = $model->convertToRedsysCurrency($cc);
		$this->key = $model->getClave();
		$this->url = $model->getRedsysUrl();
	}

	public function reembolso($amount_to_charge, $order)
	{
		if (is_null($this->merchant))
			$this->init($order);

		$ds_merchant_order = $order->getIncrementId();
		$this->call_api(
			$ds_merchant_order,
			round($amount_to_charge * 100),
			3,
			$order->getId()
		);
	}

	public function confirmar_preautorizacion($amount_to_charge, $order)
	{
		if (is_null($this->merchant))
			$this->init($order);

		$model = Mage::getSingleton('redsys/standard');
		$ds_merchant_order = $order->getDsOrder();
		$params = $order->getPayment()
			->getAuthorizationTransaction()
			->getAdditionalInformation(
				Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS
			);
		$ds_merchant_transactiontype = $params['Ds_TransactionType'];
		if (1 == $ds_merchant_transactiontype)
			$ds_merchant_transactiontype = 2;
		if (7 == $ds_merchant_transactiontype)
			$ds_merchant_transactiontype = 8;

		$this->call_api(
			$ds_merchant_order,
			round($amount_to_charge * 100),
			$ds_merchant_transactiontype,
			$order->getId()
		);
	}

	public function pago_por_referencia($amount_to_charge, $token, $merchanturl, $ds_merchant_data = null, $ds_merchant_order = null)
	{
		if (!$ds_merchant_order) {
			$ds_merchant_order = sprintf('%012d', time());
		}
		$this->call_api(
			$ds_merchant_order,
			round($amount_to_charge * 100),
			0,
			$ds_merchant_data,
			$token
		);
	}

	/**
	 * @return boolean
	 */
	public function get_debug()
	{
		return $this->debug;
	}

	private function call_api($ds_merchant_order, $ds_merchant_amount, $ds_merchant_transactiontype, $ds_merchant_data = null, $token = null)
	{
		$ds_merchant_currency = $this->currency;
		$ds_merchant_code = $this->merchant;
		$ds_merchant_terminal = $this->terminal;
		$ds_merchant_merchanturl = Mage::getModel('core/url')->sessionUrlVar(Mage::getUrl('redsys/standard/callback'));
		if (!is_null($token))
			$ds_merchant_directpayment = 'true';
		$mensaje = $ds_merchant_amount . $ds_merchant_order . $ds_merchant_code . $ds_merchant_currency .
			$ds_merchant_transactiontype . $ds_merchant_merchanturl . $token . $ds_merchant_directpayment;
		if (!class_exists("RedsysAPI")) {
			$lib = Mage::getBaseDir('lib');
			require_once($lib . '/Redsys/RedsysAPI.php');
		}
		// Generamos la firma
		$redsys = new RedsysAPI;
		$redsys->setParameter("DS_MERCHANT_AMOUNT", $ds_merchant_amount);
		$redsys->setParameter("DS_MERCHANT_ORDER", $ds_merchant_order);
		$redsys->setParameter("DS_MERCHANT_MERCHANTCODE", $ds_merchant_code);
		$redsys->setParameter("DS_MERCHANT_CURRENCY", $ds_merchant_currency);
		$redsys->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $ds_merchant_transactiontype);
		$redsys->setParameter("DS_MERCHANT_TERMINAL", $ds_merchant_terminal);
		$redsys->setParameter("DS_MERCHANT_MERCHANTURL", $ds_merchant_merchanturl);
		$redsys->setParameter("DS_MERCHANT_URLOK", Mage::getUrl('checkout/onepage/success'));
		$redsys->setParameter("DS_MERCHANT_URLKO", Mage::getUrl('redsys/standard/cancel'));
		$redsys->setParameter("Ds_Merchant_ConsumerLanguage", $this->calcLanguage(Mage::app()->getLocale()->getLocaleCode()));

		$redsys->setParameter("Ds_Merchant_MerchantData", $ds_merchant_data);

		if (!is_null($token)) {
			$redsys->setParameter("'Ds_Merchant_Identifier",$token);
			$redsys->setParameter("Ds_Merchant_DirectPayment",$ds_merchant_directpayment);
		}
		$redsys->setParameter("Ds_Merchant_PayMethods",'T');
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

		$res = str_replace(' ', '', $this->curl_request(http_build_query($sArr), $this->url));
		if (stripos($res, '<!--RSisException-->') !== false) {
			$code = 666;
			if (preg_match('/\<\!--SIS(\d\d\d\d)/', $res, $matches))
				$code = constant('ZhenIT_Redsys_Model_Client::ERROR_SIS' . $matches[1]);
			$message = Mage::helper('payment')->__('Transaction failed. %s ', $code);
			if(class_exists (Mage_Payment_Model_Info_Exception))
                throw new Mage_Payment_Model_Info_Exception($message);
            else
                throw new Mage_Core_Exception($message);
		}
	}

	private function curl_request($requestString = null, $requestUrl = 'https://sis.redsys.es/sis/realizarPago')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestUrl);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		$httpResponse = curl_exec($ch);
		return $httpResponse;
	}

} 
