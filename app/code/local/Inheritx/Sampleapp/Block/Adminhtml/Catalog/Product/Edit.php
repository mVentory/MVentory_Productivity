<?php
/**
 * Rewrite the Category edit block
 *
 * @category   Mage_Catalog_Product_Edit
 * @package    Inheritx_Sampleapp
 * @author     Inheritx Team <gaurav@inheritx.com>
 */
class Inheritx_Sampleapp_Block_Adminhtml_Catalog_Product_Edit extends Mage_Adminhtml_Block_Catalog_Product_Edit
{
	/**
	 * Add button on product edit page on admin side to redirect on product view page on store front
	 */
  protected function _prepareLayout()
    {                  
        if ($this->getProduct()->getId()) {
            $frontUrl 	=	$this->getProduct()->getUrlInStore();
            $this->setChild('frontshop_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('FrontShop Preview'),
                        'onclick'   => "window.open('".$frontUrl."','_blank');",
                    ))
            );
        }
        

        return parent::_prepareLayout();
    }
    
    
    /**
	   * Add FrontShop Preview button before Reset button
	   */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('frontshop_button').$this->getChildHtml('reset_button');
    }
}