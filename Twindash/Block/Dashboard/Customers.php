<?php
/**
 * Dashboard customer statistics
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Customers extends Twindom_Twindash_Block_Dashboard_Box
{
    protected function _prepareLayout()
    {
        $this->addTotal($this->__('Customers'), 0);
        $this->addTotal($this->__('Locations'), ' ');
    }
}
