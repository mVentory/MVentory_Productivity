<?php

class ZetaPrints_Productivity_Block_Rss_Product_Latest
  extends Mage_Rss_Block_Abstract {

  const ITEMS_NUMBER = 30;

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
    );
  }

  protected function _toHtml () {
    $products = $this
                  ->getLayout()
                  ->createBlock('productivity/product_latest')
                  ->setProductsCount(self::ITEMS_NUMBER)
                  ->getProductCollection();

    $data = array(
      'title' => $this->__('Latest products'),
      'link' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
      'images' => array()
    );

    return $this
             ->helper('productivity/rss')
             ->generateFeedForProducts($products, $data);
  }
}
