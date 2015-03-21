<?php
/**
 * Dashboard admin statistics
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_Block_Dashboard_Admins extends Twindom_Twindash_Block_Dashboard_Box
{
    protected function _prepareLayout()
    {
        $this->addTotal($this->__('Admins'), 0);
    }
}
