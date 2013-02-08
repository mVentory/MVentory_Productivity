<?php

class ZetaPrints_MvProductivityPack_Block_Rss_Product_Latest
  extends Mage_Rss_Block_Abstract {

  protected function _construct () {
    parent::_construct();

    $this
      ->addData(array(
        'cache_lifetime' => 1800,
        'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG),
      ));
  }

  /**
   * Get Key pieces for caching block content
   *
   * @return array
   */
  public function getCacheKeyInfo () {
    return array(
      'PRODUCTIVITY_RSS_PRODUCT_LATEST',
      $this->_getStoreId(),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      $this->getProductsCount()
    );
  }

  protected function _toHtml () {
    $products = $this
                  ->getLayout()
                  ->createBlock('MvProductivityPack/product_latest')
                  ->getProductCollection();

    $data = array(
      'title' => $this->__('Latest products'),
      'link' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
      'images' => array()
    );

    return $this
             ->helper('MvProductivityPack/rss')
             ->generateFeedForProducts($products, $data);
  }
}
