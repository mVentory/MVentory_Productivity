<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Attribute helper
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Helper_Attribute
  extends MVentory_Productivity_Helper_Data
{
  protected $_blacklist = array(
    //This attributes requires custom frontend renderer and are not supported
    //now
    'tier_price' => true,
    'group_price' => true,
    'is_recurring' => true,
    'recurring_profile' => true,

    //This attribute has additional control (Create Permanent Redirect
    //for old URL checkbox) which we don't render. It means we can't provide
    //expected functionality and result (as in the admin interface)
    'url_key' => true,

    //These attributes has a separate editor in our panel
    'image' => true,
    'small_image' => true,
    'thumbnail' => true,
  );

  protected $_configurableBlacklist = array(
  );

  protected $_nonReplicable = array(
    'sku' => true,
    'price' => true,
    'special_price' => true,
    'special_from_date' => true,
    'special_to_data' => true,
    'weight' => true,

    //!!!TODO: do we need to process API specific attributes here or not?
    //We can apply additional filters required by other exts using event
    //observer
    //'product_barcode_' => true,
  );

  /**
   * Return list of attributes which can be edited
   *
   * @param Mage_Catalog_Model_Product $product Product
   * @return array List of Mage_Catalog_Model_Resource_Eav_Attribute entities
   */
  public function getEditables ($product) {
    $setId = $product->getAttributeSetId();
    $isConfigurable = $product->isConfigurable();

    $attrs = $this->_getAttrs($setId);
    $allow = $this->_getAllowedAttrsByUser();
    $ignore = $isConfigurable
                ? $this->_configurableBlacklist
                  : array();

    //Add QTY pseudo-attribute before filtering so filtering rules can be
    //applied for it
    $attrs['qty'] = $this->_getQtyPseudoAttr($isConfigurable);

    //Remove attributes which are used in configurable attributes so they
    //won't appear in the fronend editor
    if ($isConfigurable)
      $attrs = $this->_removeConfigurableAttrs($attrs, $product);

    $_attrs = array();

    foreach ($attrs as $attr)
      if ((!$attr->getId() || $attr->isInSet($setId))
          && $this->_isAllowedAttribute($attr, $allow, $ignore))
        $_attrs[$attr->getAttributeCode()] = $attr;

    return $_attrs;
  }

  /**
   * Return list of attributes which can be replocated among configurable
   * products and its assigned simple products
   *
   * @param Mage_Catalog_Model_Product $product Product
   * @return array List of Mage_Catalog_Model_Resource_Eav_Attribute entities
   */
  public function getReplicables ($product) {
    $setId = $product->getAttributeSetId();

    $attrs = $this->_removeConfigurableAttrs(
      $this->_getAttrs($setId),
      $product
    );

    $allow = $this->_getReplicableAttrsByUser();

    $_attrs = array();

    foreach ($attrs as $attr)
      if ((!$attr->getId() || $attr->isInSet($setId))
          && $this->_isAllowedAttribute($attr, $allow, $this->_nonReplicable))
        $_attrs[$attr->getAttributeCode()] = $attr;

    return $_attrs;
  }

  /**
   * Return sorted list of attributes for the specified attribute set
   *
   * @param int $setId ID of atrribute set
   * @return array List of Mage_Catalog_Model_Resource_Eav_Attribute entities
   */
  protected function _getAttrs ($setId) {
    return Mage::getModel('catalog/product')
      ->getResource()
      ->loadAllAttributes()
      ->getSortedAttributes($setId);
  }

  /**
   * Check if specified attribute is allowed.
   * List of allowed attributes has higher priority over list of ignored and
   * blacklisted attributes.
   *
   * @param Mage_Catalog_Model_Resource_Eav_Attribute $attr Atrribute
   * @param array $allow Key based list of allowed attributes
   * @param array $ignore Key based list of attributes to ignore
   */
  protected function _isAllowedAttribute ($attr,
                                          $allow = array(),
                                          $ignore = array()) {

    $code = $attr->getAttributeCode();

    if (($allow && !isset($allow[$code]))
         || isset($ignore[$code])
         || isset($this->_blacklist[$code]))
      return false;

    return $attr->getIsVisible();
  }

  /**
   * Get list of allowed attributes by user
   *
   * @return array Key based list of codes of allowed attributes
   */
  protected function _getAllowedAttrsByUser () {
    $attrs = trim(
      Mage::getStoreConfig(MVentory_Productivity_Model_Config::_EDITABLE_ATTRS)
    );

    return $attrs ? $this->_parseListOfAttrs($attrs) : array();
  }

  /**
   * Get list of replicable attributes by user
   *
   * @return array Key based list of codes of replicable attributes
   */
  public function _getReplicableAttrsByUser () {
    $attrs = strtolower(trim(
      Mage::getStoreConfig(MVentory_Productivity_Model_Config::_COPY_ATTRS)
    ));

    return $attrs
             ? $this->_parseListOfAttrs($attrs)
             : $this->_getAllowedAttrsByUser();
  }

  /**
   * Parse user-defined list of attribute codes and return it as key-based array
   *
   * @param string $attrs
   *   List of attribute coes. One attribute code per line
   *
   * @return array
   *   Key-based list of attribute codes
   */
  protected function _parseListOfAttrs ($attrs) {
    return array_flip(array_map(
        'trim',
        explode("\n", str_replace(array("\r\n", "\r"), "\n", trim($attrs)))
    ));
  }

  /**
   * Return model for QTY pseudo-attribute with prefilled required fields
   *
   * @param bool $isConfigurable Show if current product is configurable one
   * @return Catalog_Model_Resource_Eav_Attribute
   */
  protected function _getQtyPseudoAttr ($isConfigurable) {
    return Mage::getModel(
      'catalog/resource_eav_attribute',
      array(
        'attribute_code' => 'qty',
        'store_label' => 'Qty',
        'frontend_input' => $isConfigurable ? 'label' : 'text',
        'uses_sources' => false,
        'is_visible' => true,
        'is_user_defined' => true,
        '_insert_after' => 'price'
      )
    );
  }

  /**
   * Filter out attributes which are used in configurable product
   *
   * @param array $attrs List of attributes to filter
   * @param Mage_Catalog_Model_Product $product Configurable product
   * @return array List of attributes
   */
  protected function _removeConfigurableAttrs ($attrs, $product) {
    $_attrs = (($_attrs = $product->getConfigurableAttributesData()) !== null)
                ? $_attrs
                  : $product
                      ->getTypeInstance()
                      ->getConfigurableAttributesAsArray();

    foreach ($_attrs as $_attr)
      unset($attrs[$_attr['attribute_code']]);

    return $attrs;
  }
}