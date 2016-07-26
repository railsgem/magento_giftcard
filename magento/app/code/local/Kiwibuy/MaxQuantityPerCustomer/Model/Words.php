<?php

class Kiwibuy_MaxQuantityPerCustomer_Model_Words
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('maxquantitypercustomer')->__('Hello')),
            array('value'=>2, 'label'=>Mage::helper('maxquantitypercustomer')->__('Goodbye')),
            array('value'=>3, 'label'=>Mage::helper('maxquantitypercustomer')->__('Yes')),            
            array('value'=>4, 'label'=>Mage::helper('maxquantitypercustomer')->__('No')),                       
        );
    }

}