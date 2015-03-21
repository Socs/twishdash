<?php
/**
 * Dashboard ajax model
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Model_Updater extends Twindom_Twindash_Block_Dashboard_Box
{

    // Master control gets updates for each block
    public function updateBlocks($dates, $ids)
    {   
        $dateArray = $this->getDateRanges($dates);
        $updateArray = array();
        foreach ($ids as $id) {
            $function = 'update' . str_replace('-', '_', $id);
            if (method_exists($this, $function)) {
                $updateArray[] = array('kpiName' => $id, 'kpiValue' => $this->$function($dateArray));
            }
            else {
                $updateArray[] = array('kpiName' => $id, 'kpiValue' => 'Update Function Missing');
            }
        }

        return $updateArray;
    }

    // Update admins
    // There is a bug here where admin count inflated by the total number of admins
    protected function updateAdmins($dates)
    {
        $adminUsersCount = array();

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('admin_user');
        $adminUsers = $readConnection->fetchAll('SELECT user_id, created FROM ' . $table);
        
        $index = 0;
        foreach($dates as $date) {
            if ($index == 0) {
                $adminUserCollection = $adminUsers;
            }
            else {
                $adminUserCollection = array();

                foreach ($adminUsers as $adminUser) {
                    if ($adminUser['created'] > $date['start'] && $adminUser['created'] <= $date['end']) {
                        $adminUserCollection[] = $adminUser['user_id'];
                    }
                }
                $index++;
            }
            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $adminUsersCount[]= array($postedDate, count($adminUserCollection));
        }
        
        return $adminUsersCount;
    }

    // Update cutomers TODO: All the below update functions hit the db for each date, proably better to sort dates
    // after first hit.
    protected function updateCustomers($dates)
    {   
        $customerCount = array();

        foreach($dates as $date) {
            $customerCollection = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('customer_entity')
                ->addAttributeToFilter('created_at', array('from'=>$date['start'], 'to'=>$date['end']));
           
            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $customerCount[] = array($postedDate, count($customerCollection));
        }

        return $customerCount;
    }

    protected function updateProducts($dates)
    {
        $productCount = array();

        foreach($dates as $date) {
            $productCollection = $this->getProductData($date);

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $productCount[] = array($postedDate, count($productCollection));
        }

        return $productCount;
    }

    protected function updateAverage_Price($dates)
    {
        $averagePriceData = array();

            foreach($dates as $date) {
                $sales = $this->getSalesData($date);
                $totalSales = $sales->getLifetime();
                $totalProducts = count($this->getProductData($date));

                $postedDate = new DateTime($date['end']);
                $postedDate = $postedDate->format('Y-m-d');
                $averagePriceData[] = array($postedDate, strip_tags($this->format($totalSales / $totalProducts)));
            }


        return $averagePriceData;
    }

    protected function updateLifetime_Sales($dates)
    {
        $lifetimeData = array();

        foreach ($dates as $date) {
            $sales = $this->getSalesData($date);

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $lifetimeData[] = array($postedDate, strip_tags($this->format($sales->getLifetime())));
        }

        return $lifetimeData;
    }

    protected function updateAverage_Order($dates)
    {
        $averageData = array();

        foreach ($dates as $date) {
            $sales = $this->getSalesData($date);

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $averageData[] = array($postedDate, strip_tags($this->format($sales->getAverage())));
        }

        return $averageData;
    }

    protected function updateOrders($dates)
    {
        $allOrderData = array();

        foreach ($dates as $date) {
            $orderCompleteCount = 0;
            $orderProcessingCount = 0;
            $orderCanceledCount = 0;

            $orderStats = $this->getOrderData($date);

            foreach ($orderStats as $orderStat) {
                if ($orderStat->status == 'canceled') {
                 $orderCanceledCount++;
                }
                elseif ($orderStat->status == 'processing') {
                    $orderProcessingCount++;
                }
                elseif ($orderStat->status == 'complete') {
                    $orderCompleteCount++;
                }
            }

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $allOrderData[] = array($postedDate, count($orderStats));
        }

        return $allOrderData;
    }

    protected function updateLocations($dates)
    {   
        $locationsData = array();

        foreach($dates as $date) {
            $location = array();
            $orderStats = $this->getOrderData($date);
            foreach ($orderStats as $orderStat) {
                $address = $orderStat->getShippingAddress();
                if (is_object($address)) {
                    $location[] = $orderStat->getShippingAddress()->getData()['country_id'];
                }
            }
           
            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $locationsData[] = array($postedDate, $location);
        }

        return $locationsData;
    }

    protected function updateOrders_Per_Customer($dates)
    {
        $precision = 2;
        $allCustomerData = array();

        foreach ($dates as $date) {
            $customerArray = array();
            $orderStats = $this->getOrderData($date);
            $totalOrders = count($orderStats);

            foreach ($orderStats as $orderStat) {
                $cutomer = $orderStat->customer_id == null ? 'no name' : $orderStat->customer_id;
                if (!in_array($cutomer, $customerArray)) $customerArray[] = $cutomer;
            }

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $allCustomerData[] = array($postedDate, round($totalOrders / count($customerArray), $precision));
        }

        return $allCustomerData;
    }

    protected function updateUnique_Items_Per_Order($dates)
    {
        $precision = 2;
        $allUniqueData = array();

        foreach ($dates as $date) {
            $totalItems = 0;
            $orderStats = $this->getOrderData($date);
            $totalOrders = count($orderStats);

            foreach ($orderStats as $orderStat) {
                $totalItems += $orderStat->total_item_count;
            }

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $allUniqueData[] = array($postedDate, round($totalItems / $totalOrders, $precision));
        }

        return $allUniqueData;
    }

    protected function updateQuantity_Per_Order($dates)
    {
        $precision = 2;
        $allQuantityData = array();

        foreach ($dates as $date) {
            $totalQty = 0;
            $orderStats = $this->getOrderData($date);
            $totalOrders = count($orderStats);

            foreach ($orderStats as $orderStat) {
                $totalQty += $orderStat->total_qty_ordered;
            }

            $postedDate = new DateTime($date['end']);
            $postedDate = $postedDate->format('Y-m-d');
            $allQuantityData[] = array($postedDate, round($totalQty / $totalOrders, $precision));
        }

        return $allQuantityData;
    }

    protected function getSalesData($date)
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');

        $collection = Mage::getResourceModel('reports/order_collection')
            ->calculateSales($isFilter);

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        }
        $collection->addAttributeToFilter('created_at', array('from'=>$date['start'], 'to'=>$date['end']));

        $collection->load();
        $sales = $collection->getFirstItem();

        return $sales;
    }

    protected function getOrderData($date)
    {
        $orderCollection = Mage::getModel('sales/order')
            ->getCollection()
            //->addFieldToSelect(array('entity_id', 'customer_id', 'status', 'total_item_count', 'total_qty_ordered'))
            ->addAttributeToFilter('created_at', array('from'=>$date['start'], 'to'=>$date['end']));

        return $orderCollection;
    }

    protected function getProductData($date)
    {
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('status')
            ->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('created_at', array('from'=>$date['start'], 'to'=>$date['end']));

        return $productCollection;
    }

    // Create the date ranges we want using the filter's information
    protected function getDateRanges($dates) {
        $periods = $dates[0];
        $type = $dates[1];
        $end = $dates[2];
        $dateArry = array();

        // Add empty array so we can get lifetime values
        $dateArry[] = array();

        for($index = 1; $index <= $periods; $index++) {
            $endDate   = new DateTime($end);
            $startDate = new DateTime($end);
            $startDate->modify('-1' . ' ' . $type);
            $dateArry[] = array('start' => $startDate->format('Y-m-d H:i:s'), 'end' => $endDate->format('Y-m-d H:i:s'));
            $end = $startDate->format('Y-m-d');
        }

        return $dateArry;

    }

}
