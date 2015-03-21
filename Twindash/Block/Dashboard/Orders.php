<?php
/**
 * Dashboard order statistics
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Orders extends Twindom_Twindash_Block_Dashboard_Box
{

    protected function _prepareLayout()
    {   
        $this->addTotal($this->__('Orders'), 0);
        $this->addTotal($this->__('Orders Per Customer'), 0);
        $this->addTotal($this->__('Unique Items Per Order'), 0);
        $this->addTotal($this->__('Quantity Per Order'), 0);
    }
}
