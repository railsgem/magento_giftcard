<?php
class Kiwibuy_AuCarrier_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'kiwibuy_aucarrier';

    public $_special_cata_price = 9;
    public $_special_category_id = 20;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        $this->_special_cata_price = $this->getConfigData('special_cata_price');
        $this->_special_category_id = $this->getConfigData('special_category_id');
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getStandardShippingRate($request));
        return $result;
    }

    protected function _getStandardShippingRate($request)
    {
        $special_cata_price = $this->_special_cata_price;
        $special_category_id = $this->_special_category_id;
        $price = $this->getConfigData('price');
        $itemQtythThreshold = $this->getConfigData('item_qty_threshold');
        $totalQtythThreshold = $this->getConfigData('total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;
        $special_cata_total_price = 0;
        $special_cata_total_qty = 0;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getQty() > $itemQtythThreshold){
                $eligibleItemQty = false;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId()); 
            $categoryIds = $product->getCategoryIds();
            if (in_array($special_category_id, $categoryIds)){
                $special_cata_total_qty = $special_cata_total_qty + $item->getQty();
            } else {
                $totalQty = $totalQty + $item->getQty(); //except special catagory
            }
        }    
        //special catagory
        $special_cata_total_price = $special_cata_total_qty * $special_cata_price;

        //except special catagory
        if ($totalQty > $totalQtythThreshold) {
            $price = $price + ($totalQty - $totalQtythThreshold) * $addPricePerItem;
        }
        if ($eligibleItemQty) {
            $price = $price + $special_cata_total_price;
            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

            $rate->setCarrier($this->_code);
            /**
             * getConfigData(config_key) returns the configuration value for the
             * carriers/[carrier_code]/[config_key]
             */
            $rate->setCarrierTitle($this->getConfigData('title'));

            $rate->setMethod('standand');
            $rate->setMethodTitle('Standard');
            $rate->setPrice($price);
            $rate->setCost(0);
        }

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            'standard' => 'Standard',
        );
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}