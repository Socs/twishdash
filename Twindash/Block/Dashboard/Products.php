<?php
/**
 * Dashboard product statistics
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Products extends Twindom_Twindash_Block_Dashboard_Box
{
    protected function _prepareLayout()
    {
        $this->addTotal($this->__('Products'), 0);
        $this->addTotal($this->__('Average Price'), 0);
    }
}
