<?php
class Kiwibuy_CnCarrier_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'kiwibuy_cncarrier';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result->append($this->_getStandardShippingRate($request));
        $result->append($this->_getDhlShippingRate($request));
        return $result;
    }

    protected function _getDhlShippingRate($request)
    {
        $range_one_price = $this->getConfigData('range_one_dhl_price');
        $range_one_itemQtythThreshold = $this->getConfigData('range_one_item_qty_threshold');
        $range_one_totalQtythThreshold = $this->getConfigData('range_one_total_qty_threshold');
        $range_two_price = $this->getConfigData('range_two_dhl_price');
        $range_two_itemQtythThreshold = $this->getConfigData('range_two_item_qty_threshold');
        $range_two_totalQtythThreshold = $this->getConfigData('range_two_total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getQty() > $range_one_itemQtythThreshold || $item->getQty() > $range_two_itemQtythThreshold){
                $eligibleItemQty = false;
            }
            $totalQty = $totalQty + $item->getQty();
        }    
        if ($totalQty <= $range_one_totalQtythThreshold) {
            $price = $range_one_price;
        } else if ($totalQty > $range_one_totalQtythThreshold && $totalQty <= $range_two_totalQtythThreshold) {
            $price = $range_two_price;
        }
        if ($totalQty > $range_two_totalQtythThreshold) {
            $price = $range_two_price + ($totalQty - $range_two_totalQtythThreshold) * $addPricePerItem;
        }
        if ($eligibleItemQty) {
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
    protected function _getStandardShippingRate($request)
    {
        $range_one_price = $this->getConfigData('range_one_price');
        $range_one_itemQtythThreshold = $this->getConfigData('range_one_item_qty_threshold');
        $range_one_totalQtythThreshold = $this->getConfigData('range_one_total_qty_threshold');
        $range_two_price = $this->getConfigData('range_two_price');
        $range_two_itemQtythThreshold = $this->getConfigData('range_two_item_qty_threshold');
        $range_two_totalQtythThreshold = $this->getConfigData('range_two_total_qty_threshold');
        $addPricePerItem = $this->getConfigData('extra_price_per_item');

        $totalQty = 0;
        $eligibleItemQty = True;

        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getQty() > $range_one_itemQtythThreshold || $item->getQty() > $range_two_itemQtythThreshold){
                $eligibleItemQty = false;
            }
            $totalQty = $totalQty + $item->getQty();
        }    
        if ($totalQty <= $range_one_totalQtythThreshold) {
            $price = $range_one_price;
        } else if ($totalQty > $range_one_totalQtythThreshold && $totalQty <= $range_two_totalQtythThreshold) {
            $price = $range_two_price;
        }
        if ($totalQty > $range_two_totalQtythThreshold) {
            $price = $range_two_price + ($totalQty - $range_two_totalQtythThreshold) * $addPricePerItem;
        }
        if ($eligibleItemQty) {
            $rate = Mage::getModel('shipping/rate_result_method');
            /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

            $rate->setCarrier($this->_code);
            /**
             * getConfigData(config_key) returns the configuration value for the
             * carriers/[carrier_code]/[config_key]
             */
            $rate->setCarrierTitle($this->getConfigData('title'));

            $rate->setMethod('standard');
            $rate->setMethodTitle('普通快递');

            $rate->setPrice($price);
            $rate->setCost(0);
        }

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            'dhl' => 'DHL',
            'standard' => '普通快递',
        );
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}