<?php

/**
 * Productivity
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  ZetaPrints
 * @package   ZetaPrints_Productivity
 * @copyright Copyright (c) 2014 ZetaPrints Ltd. (http://www.zetaprints.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Block to output attribute values
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   Anatoly A. Kazantsev <anatoly@zetaprints.com>
 */

class ZetaPrints_Productivity_Block_Widget_Attribute
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

  protected function _getAttributeSource () {
    $attr = $this->getData('attribute_source');

    if ($attr)
      return $attr;

    if (!$code = $this->getData('code'))
      return null;

    $attr = Mage::getModel('productivity/widget_attribute')
              ->loadByCode($code);

    $this->setData('attribute_source', $attr);

    return $attr;
  }

  protected function _toHtml () {
    $html = '';

    $attr = $this->_getAttributeSource();

    if (!$attr)
      return $html;

    $template = $this->getData('item_template');
    $code = $this->getData('code');

    $search = array('%code%', '%value%', '%label%');

    foreach ($attr->getOptions() as $option) {
      $replace = array(
        $code,
        $option['value'],
        $option['label'],
      );

      $html .= str_replace($search, $replace, $template);
    }

    return $html;
  }
}
