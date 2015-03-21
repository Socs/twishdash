<?php
/**
 * Dashboard ajax controller.
 * Collect data from frontend, pass along to our model, and return the processed data.
 *
 * @category   Twindom
 * @package    Twindom_Twindash
 * @author     Matt Rust
 */

class Twindom_Twindash_AjaxController extends Mage_Adminhtml_Controller_Action
{
	public function ajaxBlockUpdateAction()
	{
	    $dates = $this->getRequest()->getParam('dates');
	    $ids = $this->getRequest()->getParam('ids');
	    $ids = explode(',', $ids);
	    $dates = explode(',', $dates);
        $update = Mage::getModel('twindom_twindash/updater')->updateBlocks($dates, $ids);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($update));
	    return;
	}
}