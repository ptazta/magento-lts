<?php
class ZhenIT_Redsys_Model_System_Config_Source_Signaturemethod
{

    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('redsys')->__('Completo')),
            array('value' => 2, 'label' => Mage::helper('redsys')->__('Completo ampliado')),
        );
    }
}