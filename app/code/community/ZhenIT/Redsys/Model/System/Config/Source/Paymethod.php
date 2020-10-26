<?php
class ZhenIT_Redsys_Model_System_Config_Source_Paymethod
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('redsys')->__('Todos los disponibles')),
            array('value' => 'T', 'label' => Mage::helper('redsys')->__('Tarjeta y IUPAY!')),
            array('value' => 'C', 'label' => Mage::helper('redsys')->__('Solo tarjeta'))
        );
    }
}