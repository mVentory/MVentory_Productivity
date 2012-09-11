<?php
/**
 * Rewrite the Category tab product block to add the edit link in grid row
 *
 * @category   Inheritx_Sampleapp_Block_Adminhtml_Catalog_Category_Tab_Product
 * @package    Inheritx_Sampleapp
 * @author     Inheritx Team <gaurav@inheritx.com>
 */
class Inheritx_Sampleapp_Block_Adminhtml_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{   
    /*
     * Add Edit column to edit link on product grid under category products tab to redirect on product edit page
     * We have rewrite _prepareColumns method by creating new class in custom module 
     */
	protected function _prepareColumns()
    {
		$this->addColumnAfter('action',
            array(
                'header' => Mage::helper('catalog')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url' => array(
                            'base'=>'*/catalog_product/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
        ),'position'); 

        return parent::_prepareColumns();
    }
}
