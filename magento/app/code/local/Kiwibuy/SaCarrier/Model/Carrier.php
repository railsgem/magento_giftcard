<?php
class Kiwibuy_SaCarrier_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'kiwibuy_sacarrier';

    public $_special_cata_price = 9;
    public $_special_category_id = 20;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_special_cata_price = $this->getConfigData('special_cata_price');
        $this->_special_category_id = $this->getConfigData('special_category_id');

        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getDhlShippingRate($request));
        return $result;
    }

    protected function _getDhlShippingRate($request)
    {
        $special_cata_price = $this->_special_cata_price;
        $special_category_id = $this->_special_category_id;
        
        $range_one_price = $this->getConfigData('range_one_price');
        $range_one_itemQtythThreshold = $this->getConfigData('range_one_item_qty_threshold');
        $range_one_totalQtythThreshold = $this->getConfigData('range_one_total_qty_threshold');
        $range_two_price = $this->getConfigData('range_two_price');
        $range_two_itemQtythThreshold = $this->getConfigData('range_two_item_qty_threshold');
        $range_two_totalQtythThreshold = $this->getConfigData('range_two_total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;
        $special_cata_total_price = 0;
        $special_cata_total_qty = 0;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getQty() > $range_one_itemQtythThreshold || $item->getQty() > $range_two_itemQtythThreshold){
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
        if ($totalQty <= $range_one_totalQtythThreshold) {
            $price = $range_one_price;
        } else if ($totalQty > $range_one_totalQtythThreshold && $totalQty <= $range_two_totalQtythThreshold) {
            $price = $range_two_price;
        }
        if ($totalQty > $range_two_totalQtythThreshold) {
            $price = $range_two_price + ($totalQty - $range_two_totalQtythThreshold) * $addPricePerItem;
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

            $rate->setMethod('dhl');
            $rate->setMethodTitle('DHL');

            $rate->setPrice($price);
            $rate->setCost(0);
        }

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            'dhl' => 'DHL',
        );
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}