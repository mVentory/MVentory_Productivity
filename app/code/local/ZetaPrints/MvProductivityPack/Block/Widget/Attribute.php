<?php
/**
 * Block to output attribute values
 *
 * @category ZetaPrints_MvProductivityPack_Block_Widget_Attribute
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */
class ZetaPrints_MvProductivityPack_Block_Widget_Attribute
  extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

  protected function _construct () {
    parent::_construct();

    $this->setData('cache_lifetime', 86400);
  }

  public function getCacheKeyInfo () {
    return array(
      'EAV_ATTRIBUTE_VALUE_LIST',
      Mage::app()->getStore()->getId(),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      'template' => $this->getData('item_template'),
      'code' => $this->getData('code'),
    );
  }

  public function getValues () {
    if ($values = $this->getData('values'))
      return $values;

    $this->setData('values', $values = array());

    if (!$code = $this->getData('code'))
      return $values;

    $attr = Mage::getModel('eav/entity_attribute')
              ->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);

    if (!($attr->getId() && $attr->usesSource()))
      return $values;

    $values = $attr
                ->getSource()
                ->getAllOptions(false);

    $this->setData('values', $values);

    return $values;
  }

  protected function _toHtml () {
    $template = $this->getData('item_template');
    $code = $this->getData('code');

    $search = array('%code%', '%value%', '%label%');

    $html = '';

    foreach ($this->getValues() as $value) {
      $replace = array(
        $code,
        $value['value'],
        $value['label'],
      );

      $html .= str_replace($search, $replace, $template);
    }

    return $html;
  }
}
