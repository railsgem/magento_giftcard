<?php
class Kinex_MaxQuantityAmount_Helper_Data extends Mage_Core_Helper_Abstract
{
    
     const XML_PATH_ACTIVE = 'quantityoptionconfig/productquantity_group/productquantity_enable';
           const XML_PATH_MAX_QUANTITY_MSG = 'quantityoptionconfig/productquantity_group/productquantity_limit_msg'; 



            public function isModuleEnabled($moduleName = null)
    {
        if ((int)Mage::getStoreConfig(self::XML_PATH_ACTIVE, Mage::app()->getStore()) != 1) {
            return false;
        }
        else{

           return true; 
        }
        
        
    }




          public function getMaxQuantityMsg($moduleName = null)
    {
       
           return Mage::getStoreConfig(self::XML_PATH_MAX_QUANTITY_MSG, Mage::app()->getStore()); 
       
        
        
    }



}