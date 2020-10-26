<?php

class ZhenIT_Redsys_StandardController extends Mage_Core_Controller_Front_Action {
	protected function _expireAjax() {
		if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}

	/**
	 * Get singleton with redsys standard order transaction information
	 *
	 * @return ZhenIT_Redsys_Model_Standard
	 */
	public function getStandard() {
		return Mage::getSingleton('redsys/standard');
	}

	public function visaAction() {
		$this->loadLayout();
		$this->getLayout()->getBlock('root')->setTemplate('page/3columns.phtml');
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('redsys/standard_visa'));
		$this->renderLayout();
	}

	public function mastercardAction() {
		$this->loadLayout();
		$this->getLayout()->getBlock('root')->setTemplate('page/3columns.phtml');
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('redsys/standard_mastercard'));
		$this->renderLayout();
	}

	/**
	 * When a customer chooses Redsys on Checkout/Payment page
	 *
	 */
	public function redirectAction() {
		$session = Mage::getSingleton('checkout/session');
		$session->setRedsysStandardQuoteId($session->getQuoteId());
		$order = Mage::getModel('sales/order')->load($session->getLastOrderId());
		if ($session->getLastOrderId()) {
			$this->loadLayout();
			if('redsys_standard' == $order->getPayment()->getMethod())
				$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('redsys/standard_redirect'));
			else
				$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('redsys/iupay_redirect'));
			$this->renderLayout();
		}
	}

	/**
	 * Esta función se ejecuta cuando el TPV redirecciona al usuario
	 * a la URLKO. Es decir, el proceso de pago ha fallado,
	 * y simplemente se redirecciona al usuario de vuelta a la tienda,
	 * pudiéndosele mostrar un mensaje sobre el fallo de la transacción.
	 * Aquí no se debe actualizar el pedido, el stock, ni nada por el estilo,
	 * eso debe hacerse en callbackAction.
	 */
	public function cancelAction() {
		$params  = $this->getRequest()->getParams();
		$session = Mage::getSingleton('checkout/session');
		$order   = Mage::getModel('sales/order')->load($session->getLastOrderId());
		$model   = Mage::getModel('redsys/standard');
		$message = '';
		$redsys  = $model->getRedsysData($params);
		if (count($params) > 0) {
			if ($model->firmaValida($params)) {
				$message = Mage::helper('redsys')->__($this->comentarioReponse($redsys->getParameter('Ds_Response'), $redsys->getParameter('Ds_PayMethod')));
				$comment = Mage::helper('redsys')->__('Pedido cancelado desde Redsýs con error cod. %s - %s', $redsys->getParameter('Ds_Response'), $message);

				/**
				 * Redireccionamos al carrito avisando del error.
				 */
				$session->addError($comment);
			} else {
				$session->addError(Mage::helper('redsys')->__('Transaccion denegada o cancelada desde Redsýs.'));
			}
		} else {
			$session->addError(Mage::helper('redsys')->__('Transaccion denegada o cancelada desde Redsýs.'));
		}

		$this->_redirect('sales/order/reorder', array('order_id' => $order->getId()));
	}

	/**
	 * Aqui se recoge la respuesta del TPV informando acerca
	 * de la transaccion. Esta funcion es la que maneja la
	 * notificacion online por parte del TPV.
	 */
	public function callbackAction() {
		/**
		 * Indicador para determinar si la transacción fue autorizada o no.
		 */
		$params      = $this->getRequest()->getParams();
		$model       = Mage::getSingleton('redsys/standard');
		$orderStatus = ZhenIT_Redsys_Model_Standard::STATUS_REJECTED;
		$orderState  = Mage_Sales_Model_Order::STATE_PROCESSING;

		if (count($params) > 0) {
			$redsys             = $model->getRedsysData($params);
			$Ds_MerchantData    = $redsys->getParameter('Ds_MerchantData');
			$Ds_Response        = $redsys->getParameter('Ds_Response');
			$Ds_TransactionType = $redsys->getParameter('Ds_TransactionType');
			if ($model->firmaValida($params)) {
				$comment = null;
				/**
				 * Hay que analizar DS_RESPONSE para saber el resultado de la
				 * transaccion
				 */
				if ($Ds_Response >= '0000' && $Ds_Response <= '0099') {
					$comment = '';
					switch ($Ds_TransactionType) {
						case 0:
							$comment .= 'Pago';
							break;
						case 2:
							$comment .= 'Autorización';
							break;
						case 4:
							$comment .= 'Confirmación de autorización';
							break;
						case 7:
							$comment .= 'Autenticación';
							break;
						case 8:
							$comment .= 'Confirmación de autenticacación';
							break;
					}
					$comment .= ' con exíto (codigo: %s)';
					$orderStatus = $model->getSuccessStatus();
				} elseif ($Ds_Response == '0900') {
					$comment     = 'Transaccion autorizada para devoluciones y confirmaciones (codigo: %s)';
					$orderState  = Mage_Sales_Model_Order::STATE_PROCESSING;
					$orderStatus = ZhenIT_Redsys_Model_Standard::STATUS_REFUNDED;
				} elseif ($Ds_Response == '0930') {
					/**
					 * El codigo 0930 no aparece en la documentacion que tengo de la caixa.
					 * No obstante, lo dejo como en la extension original.
					 */
					$orderStatus = $model->getConfigData('paid_status');
					if ($redsys->getParameter('Ds_PayMethod') == 'R') {
						$comment = 'Pago realizado por Transferencia bancaria';
					} else {
						$comment = 'Pago realizado por Domiciliacion bancaria';
					}
					$orderStatus = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
					$orderState  = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
				} else {
					$comment = $this->comentarioReponse($Ds_Response, $redsys->getParameter('Ds_PayMethod'));
					if ('0' == $Ds_TransactionType)
						$orderState = Mage_Sales_Model_Order::STATE_CANCELED;
				}
			} else {
				$comment     = 'Firma no válida Hacking Attenpt IP:' . $_SERVER['REMOTE_ADDR'];
				$orderStatus = Mage_Sales_Model_Order::STATUS_FRAUD;
				$orderState  = Mage_Sales_Model_Order::STATE_CANCELED;
			}
			$comment .= '<br/>Tipo de transacción ' . $Ds_TransactionType;
			$order = Mage::getModel('sales/order');

			/**
			 * Cargamos el pedido
			 */
			//$order->loadByIncrementId($Ds_Order);
			$order->load($Ds_MerchantData);

			/**
			 * Si el pedido existe (que es lo logico, puesto
			 * que estamos recibiendo confirmacion de pago)
			 */
			if ($order->getId()) {
				/**
				 * Si esta configurado asi en el backend, mandamos al
				 * cliente email avisando de su pedido.
				 */
				if (((int)$model->getConfigData('sendmailorderconfirmation')) == 1
					&& $orderStatus == $model->getConfigData('paid_status')
				) {
					$order->sendNewOrderEmail();
				}
				/**
				 * Actualizamos al nuevo estado del pedido (el nuevo estado
				 * se configura en el backend de la extension redsys)
				 */
				if (Mage_Sales_Model_Order::STATE_CANCELED == $orderState && '0' == $Ds_TransactionType) {
					$order->cancel();
					echo 'order-updated → cancelled';
				} else {
					$payment = $order->getPayment();
					$payment->setTransactionId($redsys->getParameter('Ds_Order'))
						->setStatus('APPROVED')
						->setTransactionAdditionalInfo(
							Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
							$params
						);
					/**
					 * Si es un cargo en firme hcemos factura
					 */
					if (ZhenIT_Redsys_Model_Standard::STATUS_PAID == $orderStatus) {
						$invoice = $order->prepareInvoice();
						$invoice->register()->capture();
						$invoice->setTransactionId($payment->getTransactionId());
						Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder())
							->save();

						$comment .= Mage::helper('redsys')->__('<br />Factura %s creada', $invoice->getIncrementId());

						/**
						 * Si esta asi configurado, mandamos email al cliente
						 * con la factura.
						 */
						if (((int)$model->getConfigData('sendmailorderconfirmation')) == 1) {
							$invoice->sendEmail();
						}
					}

					/*Si es una confirmació no volvemos a cambiar el estado*/
					if (!in_array($Ds_TransactionType, array('2', '8')))
						$order->setState($orderState, $orderStatus, Mage::helper('redsys')->__($comment, $Ds_Response), true)
							->setDsAuthorisationcode($redsys->getParameter('Ds_AuthorisationCode'))
							->setDsOrder($redsys->getParameter('Ds_Order'));

					//if (in_array($params['Ds_TransactionType'], array('0', '2', '3', '8')))
					if (in_array($Ds_TransactionType, array('0')))
						/*Sólo se permite un cargo, si se hace lo cerramos*/
						$payment->setIsTransactionClosed(1);
					else
						$payment->setIsTransactionClosed(0);

					if ('3' == $Ds_TransactionType)
						$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
					if ('7' == $Ds_TransactionType || '8' == $Ds_TransactionType)
						$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
				}

				$order->save();
				echo 'order-updated';
			} else {
				die('hacking-attempt');
			}
		} else {
			die('no-params');
		}

	}

	/**
	 * Retorna el comentario en base al codigo de response
	 */
	public function comentarioReponse($Ds_Response, $Ds_PayMethod) {
		switch ($Ds_Response) {
			case '101':
				return 'Tarjeta caducada';
				break;
			case '102':
				return 'Tarjeta en excepcion transitoria o bajo sospecha de fraude';
				break;
			case '104':
				return 'Operacion no permitida para esa tarjeta o terminal';
				break;
			case '116':
				return 'Disponible insuficiente';
				break;
			case '118':
				return 'Tarjeta no registrada';
				break;
			case '129':
				return 'Codigo de seguridad (CVV2/CVC2) incorrecto';
				break;
			case '180':
				return 'Tarjeta ajena al servicio';
				break;
			case '184':
				return 'Error en la autenticacion del titular';
				break;
			case '190':
				return 'Denegacion sin especificar Motivo';
				break;
			case '191':
				return 'Fecha de caducidad erronea';
				break;
			case '202':
				return 'Tarjeta en excepcion transitoria o bajo sospecha de fraude con retirada de tarjeta';
				break;
			case '0930':
				if ($Ds_PayMethod == 'R') {
					return 'Realizado por Transferencia bancaria';
				} else {
					return 'Realizado por Domiciliacion bancaria';
				}
				break;
			case '912':
			case '9912':
				return 'Emisor no disponible';
				break;
			default:
				return 'Transaccion denegada';
				break;
		}
	}
}
