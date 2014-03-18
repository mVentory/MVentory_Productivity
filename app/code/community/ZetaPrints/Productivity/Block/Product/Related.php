<?php
/**
 * Related products block
 *
 * @category ZetaPrints_Productivity_Block_Product_Latest
 * @package  ZetaPrints_Productivity
 * @author ZetaPrints
 */
class ZetaPrints_Productivity_Block_Product_Related
  extends Mage_Catalog_Block_Product_Abstract {

  public function getCacheKeyInfo () {
    return array_merge(
      parent::getCacheKeyInfo(),
      array(
        Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ($product = $this->getProduct()) ? $product->getId() : null,
        $this->getData('attribute_code'),
        $this->getData('product_count')
      )
    );
  }

  public function getProductCollection () {
    $collection = $this->getData('product_collection');

    if ($collection)
      return $collection;

    $this->setData(
      'product_collection',
      $collection = new Varien_Data_Collection()
    );

    if (!(($product = $this->getProduct())
          && ($productId = $product->getId())))
      return $collection;

    if (!$code = trim($this->getData('attribute_code')))
      return $collection;

    if (($value = $product->getData($code)) === null)
      return $collection;

    $visibility = Mage::getSingleton('catalog/product_visibility')
                    ->getVisibleInCatalogIds();

    $imageFilter = array('nin' => array('no_selection', ''));

    $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(array(
                        'name',
                        'special_price',
                        'special_from_date',
                        'special_to_date'
                      ))
                    ->addPriceData()
                    ->addUrlRewrite()
                    ->addIdFilter($productId, true)
                    ->addAttributeToFilter($code, $value)
                    ->addAttributeToFilter('small_image', $imageFilter)
                    ->setVisibility($visibility)
                    ->addStoreFilter()
                    ->setPageSize($this->getProductsCount())
                    ->setCurPage(1);

    $collection
      ->getSelect()
      ->order(new Zend_Db_Expr('RAND()'));

    return $collection;
  }
}
