<?php 
class Openxcell_Adminneworderemail_Model_Options {
    public function toOptionArray()
    {
        return array(
            array('value'=>'csv', 'label'=>Mage::helper('adminneworderemail')->__('CSV')),
            array('value'=>'xml', 'label'=>Mage::helper('adminneworderemail')->__('XML')),
        );
    }
}
