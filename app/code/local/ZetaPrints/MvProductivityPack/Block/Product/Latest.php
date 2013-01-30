<?php

class ZetaPrints_MvProductivityPack_Block_Product_Latest
  extends Mage_Catalog_Block_Product_Abstract {

  protected $_productsCount = null;

  const DEFAULT_PRODUCTS_COUNT = 6;

  /**
   * Initialize block's cache
   */
  protected function _construct () {
    parent::_construct();

    $this
      ->addColumnCountLayoutDepend('two_columns_left', 3)
      ->addColumnCountLayoutDepend('two_columns_right', 3);

    $this
      ->addData(array(
        'cache_lifetime' => 86400,
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
      'CATALOG_PRODUCT_LATEST',
      Mage::app()->getStore()->getId(),
      Mage::getDesign()->getPackageName(),
      Mage::getDesign()->getTheme('template'),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      'template' => $this->getTemplate(),
      $this->getProductsCount()
    );
  }

  /**
   * Prepare collection with new products and applied page limits.
   *
   * return Mage_Catalog_Block_Product_New
   */
  protected function _beforeToHtml() {
    $visibility = Mage::getSingleton('catalog/product_visibility')
                    ->getVisibleInCatalogIds();

    $collection = Mage::getResourceModel('catalog/product_collection')
                    ->setVisibility($visibility);

    $collection = $this
                    ->_addProductAttributesAndPrices($collection)
                    ->addStoreFilter()
                    ->addAttributeToSort('entity_id', 'desc')
                    ->setPageSize($this->getProductsCount())
                    ->setCurPage(1);

    $this->setProductCollection($collection);

    return parent::_beforeToHtml();
  }

  /**
   * Set how much product should be displayed at once.
   *
   * @param $count
   * @return Mage_Catalog_Block_Product_New
   */
  public function setProductsCount ($count) {
    $this->_productsCount = $count;

    return $this;
  }

  /**
   * Get how much products should be displayed at once.
   *
   * @return int
   */
  public function getProductsCount () {
    if (null === $this->_productsCount)
      $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;

    return $this->_productsCount;
  }
}
