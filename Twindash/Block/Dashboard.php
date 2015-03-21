<?php
/**
 * Dashboard Block
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard extends Mage_Adminhtml_Block_Dashboard
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('dashboard/twindash/index.phtml');

    }

    protected function _prepareLayout()
    {
        $this->setChild('admins',
            $this->getLayout()->createBlock('twindom_twindash/dashboard_admins')
        );
        $this->setChild('customers',
            $this->getLayout()->createBlock('twindom_twindash/dashboard_customers')
        );
        $this->setChild('orders',
            $this->getLayout()->createBlock('twindom_twindash/dashboard_orders')
        );
        $this->setChild('products',
            $this->getLayout()->createBlock('twindom_twindash/dashboard_products')
        );
        $this->setChild('sales',
            $this->getLayout()->createBlock('twindom_twindash/dashboard_sales')
        );
    }
}
