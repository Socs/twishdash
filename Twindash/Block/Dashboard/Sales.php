<?php
/**
 * Dashboard sale statistics
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Sales extends Twindom_Twindash_Block_Dashboard_Box
{
    protected function _prepareLayout()
    {
        $this->addTotal($this->__('Lifetime Sales'), 0, false);
        $this->addTotal($this->__('Average Order'), 0, false);
    }
}
