<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Giftvoucher History Model
 * 
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */

class Magestore_Giftvoucher_Model_History extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('giftvoucher/history');
    }

    /**
     * Filter Gift Card history
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @param int $action
     * @return Magestore_Giftvoucher_Model_History
     */
    public function getCollectionByOrderAction($giftVoucher, $order, $action)
    {
        return $this->getCollection()
                ->addFieldToFilter('giftvoucher_id', $giftVoucher->getId())
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('order_increment_id', $order->getIncrementId());
    }

    /**
     * Get the total amount of Gift Card spent in order
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getTotalSpent($giftVoucher, $order)
    {
        $total = 0;
        foreach ($this->getCollectionByOrderAction($giftVoucher, $order, 
            Magestore_Giftvoucher_Model_Actions::ACTIONS_SPEND_ORDER) as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }

    /**
     * Get the total amount of Gift Card refunded in order
     * 
     * @param Magestore_Giftvoucher_Model_Giftvoucher $giftVoucher
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getTotalRefund($giftVoucher, $order)
    {
        $total = 0;
        foreach ($this->getCollectionByOrderAction($giftVoucher, $order, 
            Magestore_Giftvoucher_Model_Actions::ACTIONS_REFUND) as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }
    /**
     * Add Gift Card data to customer account
     *
     * @param 
     * @return Mage_Sales_Model_Order
     */
    public function _addGiftVoucherForCollection($product)
    {
        $url = Mage::getSingleton('core/session')->getLastUrl();
        $time = time();
        $giftVoucher = Mage::getModel('giftvoucher/giftvoucher');
        $amount = $product->getPrice();
        $giftVoucher->setBalance($amount)->setAmount($amount);

        $giftProduct = Mage::getModel('giftvoucher/product')->loadByProduct($product);
        $giftVoucher->setDescription($giftProduct->getGiftcardDescription());
        if ($giftProduct->getId()) {
            $conditionsArr = unserialize($giftProduct->getConditionsSerialized());
            $actionsArr = unserialize($giftProduct->getActionsSerialized());
            if (!empty($conditionsArr) && is_array($conditionsArr)) {
                $giftVoucher->getConditions()->loadArray($conditionsArr);
            }
            if (!empty($actionsArr) && is_array($actionsArr)) {
                $giftVoucher->getActions()->loadArray($actionsArr);
            }
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $customerName = Mage::getSingleton('customer/session')->getCustomer()->getName();
        $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $giftVoucher->setCustomerId($customerId);
        $giftVoucher->setCustomerName($customerName);
        $giftVoucher->setCustomerEmail($customerEmail);
        $giftVoucher->setGiftcardTemplateImage('default.png');
        $store = Mage::app()->getStore();
        $giftVoucher->setCurrency($store->getCurrentCurrencyCode());
        $giftVoucher->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE);

        if ($timeLife = Mage::helper('giftvoucher')->getGeneralConfig('expire', $store->getStoreId())) {
            $orderTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
            $expire = date("Y-m-d H:i:s", strtotime($orderTime . ' +' . $timeLife . ' days'));
            $giftVoucher->setExpiredAt($expire);
        }

        $giftVoucher->setStoreId($store->getStoreId());

        $giftVoucher->setAction(Magestore_Giftvoucher_Model_Actions::ACTIONS_CREATE)
            ->setComments(Mage::helper('giftvoucher')->__('Created for Scheme %s', $product->getName()))
            ->setProductId($product->getId())
            ->setExtraContent(Mage::helper('giftvoucher')->__('Created by customer %s ', $customerName))
            ->setIncludeHistory(true);


        try {
            //  die(now(true));
            $giftVoucher->save();
            $timeSite = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
                Mage::getModel('giftvoucher/customervoucher')
                    ->setCustomerId($customerId)
                    ->setVoucherId($giftVoucher->getId())
                    ->setAddedDate($timeSite)
                    ->save();
            Mage::getSingleton('core/session')->addSuccess('优惠券领取成功');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('优惠券领取失败');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
    }
}
