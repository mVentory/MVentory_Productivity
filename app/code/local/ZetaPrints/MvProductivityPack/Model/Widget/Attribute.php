<?php
/**
 * Attribute source for attribute widget
 *
 * @category ZetaPrints_MvProductivityPack_Block_Widget_Attribute
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */

class ZetaPrints_MvProductivityPack_Model_Widget_Attribute {

  //TODO: convert to singleton

  const OPTIONS_ONLY_WITH_RESULTS
    = Mage_Catalog_Model_Layer_Filter_Attribute::OPTIONS_ONLY_WITH_RESULTS;

  protected $_attributeModel = null;

  /**
   * Load by attribute code
   *
   * @param string $code
   *
   * @return ZetaPrints_MvProductivityPack_Model_Widget_Attribute
   */
  public function loadByCode ($code) {
    $attribute = Mage::getModel('eav/entity_attribute')
                   ->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);

    if ($attribute->getId() && $attribute->usesSource())
      $this->_attributeModel = $attribute;

    return $this;
  }

  /**
   * Code is taken from
   * Mage_Catalog_Model_Layer_Filter_Attribute::_getItemsData() method
   *
   * @return array|null
   */
  public function getOptions () {
    if (!$attribute = $this->_attributeModel)
      return;

    $store = Mage::app()->getStore();
    $category = Mage::getModel('catalog/category')
                  ->load($store->getRootCategoryId());

    if (!$categoryId = $category->getId())
      return;

    $storeId = $store->getId();
    $aggregator = Mage::getSingleton('catalogindex/aggregation');

    $key = 'STORE_' . $storeId
           . '_CAT_' . $categoryId
           . '_CUSTGROUP_'
           . Mage::getSingleton('customer/session')->getCustomerGroupId()
           . '_' . $attribute->getAttributeCode();

    if ($data = $aggregator->getCacheData($key))
      return $data;

    $options = $attribute->getFrontend()->getSelectOptions();

    //Fake 'filter' model with required fields for
    //Mage_Catalog_Model_Resource_Layer_Filter_Attribute::getCount() method
    $object = new Varien_Object(array(
      'layer' => new Varien_Object(array(
        'product_collection' => $this->_getProductCollection($category)
      )),
      'attribute_model' => $attribute,
      'store_id' => $storeId
    ));

    $optionsCount = Mage::getResourceModel('catalog/layer_filter_attribute')
                      ->getCount($object);

    $helper = Mage::helper('core/string');
    $onlyWithResult = $attribute->getIsFilterable()
                        == self::OPTIONS_ONLY_WITH_RESULTS;

    $data = array();

    foreach ($options as $option) {
      if (is_array($option['value']))
        continue;

      if ($onlyWithResult && empty($optionsCount[$option['value']]))
        continue;

      if (!$helper->strlen($option['value']))
        continue;

      $data[] = $option;
    }

    $tags = array(
      Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId(),
      Mage_Catalog_Model_Category::CACHE_TAG . $categoryId
    );

    $aggregator->saveCacheData($data, $key, $tags);

    return $data;
  }

  /**
   * Initialise product collection from the category
   *
   * Code is taken from Mage_Catalog_Model_Layer::prepareProductCollection()
   * method
   *
   * @param Mage_Catalog_Model_Category $category
   *
   * @return Mage_Catalog_Model_Resource_Product_Collection
   */
  protected function _getProductCollection ($category) {
    $productAttributes = Mage::getSingleton('catalog/config')
                           ->getProductAttributes();

    $collection = $category
      ->getProductCollection()
      ->addAttributeToSelect($productAttributes)
      ->addMinimalPrice()
      ->addFinalPrice()
      ->addTaxPercents()
      ->addUrlRewrite($category->getId());

    Mage::getSingleton('catalog/product_status')
      ->addVisibleFilterToCollection($collection);

    Mage::getSingleton('catalog/product_visibility')
      ->addVisibleInCatalogFilterToCollection($collection);

    return $collection;
  }
}
