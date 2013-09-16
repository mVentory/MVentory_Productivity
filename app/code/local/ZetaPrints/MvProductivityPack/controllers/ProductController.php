<?php
/**
 * Override product controller to check the admin login on store front.
 * If an action doesn't exist here it will fallback to Mage_Catalog_ProductController.
 *
 * @category   Mage_Catalog
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <daniel@clockworkgeek.com>
 */

class ZetaPrints_MvProductivityPack_ProductController extends Mage_Core_Controller_Front_Action
{

  /**
   * Save new product details then redirect to product page.
   * Reviewer will see the page refresh.
   */
  public function saveAction() {
    // URL parameter
    $productId = $this->getRequest()->getParam('id');
    // Useful stuff
    $storeId = Mage::app()->getStore()->getId();
    $helper = Mage::helper('MvProductivityPack');

    if ($helper->isReviewerLogged())
    {
      // Product saves must be made from admin store
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

      $product = Mage::getModel('catalog/product');
      // Product's store is set back to frontend so correct values are loaded+saved
      $product->setStoreId($storeId)
          ->load($productId);
      // Only allow certain attributes to be set, if missing product will not be changed.
      $data = array_intersect_key(
        $this->getRequest()->getPost(),
        $helper->getVisibleAttributes($product)
      );
      if (isset($data['description'])) {
        $data['short_description'] = $data['description'];
      }
      $product->addData($data)
          ->save();

      // Reset store to be neat
      Mage::app()->setCurrentStore($storeId);
    }

    // Always show same page, even if nothing has changed
    $this->_redirectReferer(Mage::getUrl('catalog/product/view', array(
      'id'      => $productId,
      '_store'    => $storeId,
      '_use_rewrite'  => true
    )));
  }

}

