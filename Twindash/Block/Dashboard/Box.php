<?php
/**
 *  Dashboard box block
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Box extends Mage_Adminhtml_Block_Dashboard_Abstract
{
    protected $_totals = array();
    protected $_currentCurrencyCode = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dashboard/twindash/bar.phtml');
    }

    protected function getTotals()
    {
        return $this->_totals;
    }

    public function addTotal($label, $value, $isQuantity=true, $round=false)
    {   

        if (!$isQuantity) {
            $value = $this->format($value);
            $decimals = substr($value, -2);
            $value = substr($value, 0, -2);
        }else {
            $value = ($value != '') ? $value : 0;
            $decimals = '';
        }
        $this->_totals[] = array(
            'label' => $label,
            'value' => $value,
            'decimals' => $decimals,
        );

        return $this;
    }

    /**
     * Formating value specific for this store
     *
     * @param decimal $price
     * @return string
     */
    public function format($price)
    {
        return $this->getCurrency()->format($price);
    }

    /**
     * Setting currency model
     *
     * @param Mage_Directory_Model_Currency $currency
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * Retrieve currency model if not set then return currency model for current store
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrency()
    {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $this->_currentCurrencyCode = Mage::app()->getStore($this->getRequest()->getParam('store'))->getBaseCurrency();
            } else if ($this->getRequest()->getParam('website')){
                $this->_currentCurrencyCode = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getBaseCurrency();
            } else if ($this->getRequest()->getParam('group')){
                $this->_currentCurrencyCode =  Mage::app()->getGroup($this->getRequest()->getParam('group'))->getWebsite()->getBaseCurrency();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrency();
            }
        }

        return $this->_currentCurrencyCode;
    }
}
