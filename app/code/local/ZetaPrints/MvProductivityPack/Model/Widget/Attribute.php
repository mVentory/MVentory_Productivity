<?php
/**
 * Attribute source for attribute widget
 *
 * @category ZetaPrints_MvProductivityPack_Block_Widget_Attribute
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */

class ZetaPrints_MvProductivityPack_Model_Widget_Attribute
  extends Mage_Catalog_Model_Layer_Filter_Attribute {

  public function loadByCode ($code) {
    $attr = Mage::getModel('eav/entity_attribute')
              ->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);

    if (!($attr->getId() && $attr->usesSource()))
      return $this;

    return $this->setAttributeModel($attr);
  }

  public function getOptions () {
    return $this->_getItemsData();
  }
}
