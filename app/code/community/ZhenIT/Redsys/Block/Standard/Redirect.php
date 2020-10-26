<?php
class ZhenIT_Redsys_Block_Standard_Redirect extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $standard = Mage::getModel('redsys/standard');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getRedsysUrl())
            ->setId('redsys_standard_checkout')
            ->setName('Redsys')
            ->setMethod('POST')
            ->setUseContainer(true);

        foreach($standard->getStandardCheckoutFormFields() as $field => $value)
        {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $this->setFormRedirect($form->toHtml());
        $this->setWindowstate($standard->getConfigData('windowstate'));
    }
}