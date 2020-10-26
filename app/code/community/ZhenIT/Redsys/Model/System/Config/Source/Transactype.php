<?php
class ZhenIT_Redsys_Model_System_Config_Source_TransacType
{

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('redsys')->__('Autorizacion (Cargo)')),
            array('value' => 1, 'label' => Mage::helper('redsys')->__('Preautorizacion (Reserva de saldo)')),
/*            array('value' => 2, 'label' => Mage::helper('redsys')->__('Confirmacion')),
            array('value' => 3, 'label' => Mage::helper('redsys')->__('Devolucion Automatica')),
            array('value' => 4, 'label' => Mage::helper('redsys')->__('Pago Referencia')),
            array('value' => 5, 'label' => Mage::helper('redsys')->__('Transaccion Recurrente')),
            array('value' => 6, 'label' => Mage::helper('redsys')->__('Transaccion Sucesiva')),
*/
            array('value' => 7, 'label' => Mage::helper('redsys')->__('Autenticacion (validación la tarjeta)')),
/*            array('value' => 8, 'label' => Mage::helper('redsys')->__('Confirmacion de Autenticacion')),
            array('value' => 9, 'label' => Mage::helper('redsys')->__('Anulacion de Preautorizacion')),
*/
        );
    }
}