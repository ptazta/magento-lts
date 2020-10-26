<?php
class ZhenIT_Redsys_Model_System_Config_Source_Windowstate
{

    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('redsys')->__('POP Up - Estandard')),
            array('value' => 2, 'label' => Mage::helper('redsys')->__('Pantalla completa - En la misma ventana')),
        );
    }
}