<?php
/**
 * Rewrite the Category edit form block
 *
 * @category   Mage_Catalog_Product_Edit
 * @package    Inheritx_Sampleapp
 * @author     Inheritx Team <gaurav@inheritx.com>
 */
class Inheritx_Sampleapp_Block_Adminhtml_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{   
	  /**
	   * Add FrontShop Preview button 
	   */
    protected function _prepareLayout()
    {                  
        $category = $this->getCategory();
        $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category

        if (!in_array($categoryId, $this->getRootIds()) && $category->isDeleteable() && $categoryId) {
            $frontUrl 	=	$category->getUrl();
            $this->setChild('frontshop_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('FrontShop Preview'),
                        'onclick'   => "window.open('".$frontUrl."','_blank');",
                    ))
            );
            $this->_additionalButtons['frontshop'] = 'frontshop_button';
        }
        

        return parent::_prepareLayout();
    }
}
