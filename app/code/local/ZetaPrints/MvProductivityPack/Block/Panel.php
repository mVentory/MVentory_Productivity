<?php
/**
 * Block for frontend top panel
 *
 * @category ZetaPrints_MvProductivityPack_Block_Panel
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */
class ZetaPrints_MvProductivityPack_Block_Panel
  extends Mage_Core_Block_Template {

  protected function _getType () {
    return $this->getRequest()->getControllerName();
  }

  protected function _getProductLink () {
    $params['id'] = Mage::registry('product')->getId();

    return Mage::helper('adminhtml')
             ->getUrl('adminhtml/catalog_product/edit/', $params);
  }

  protected function _getCmsPageLink () {
    $page = $this->getHelper('cms/page')->getPage();

    if (!$pageId = $page->getPageId())
      return false;

    $params['page_id'] = $pageId;

    return Mage::helper('adminhtml')
             ->getUrl('mvproductivitypack/adminhtml_index/index/', $params);
  }

  protected function _getCategoryLink () {
    if (!$category = Mage::registry('current_category'))
      return false;

    $params['id'] = $category->getId();

    return Mage::helper('adminhtml')
             ->getUrl('adminhtml/catalog_category/edit/', $params);
  }

  protected function _getWithoutImagesLink () {
    $params['_current'] = true;
    $params['_use_rewrite'] = true;
    $params['_query'] = array('without_images_only' => true);

    return Mage::getUrl('*/*/*', $params);
  }
}
