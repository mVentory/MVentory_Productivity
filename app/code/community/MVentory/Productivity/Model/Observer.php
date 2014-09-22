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
 * Event observers
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Model_Observer {
  public function addCategoryFeed ($observer) {
    $helper = Mage::helper('core/url');

    $url = $helper->getCurrentUrl();
    $url = $helper->addRequestParam($url, array('rss' => 1));

    Mage::helper('productivity/rss')->addFeedToHeader($url);
  }

  public function addLatestProductsFeed ($observer) {
    $helper = Mage::helper('productivity/rss');

    $title = $helper->__('Latest products');
    $url = Mage::getUrl('productivity/rss_product/latest');

    $helper->addFeedToHeader($url, $title);
  }

  public function addTopCategoriesFeed ($observer) {
    $helper = Mage::helper('productivity/rss');

    $title = $helper->__('Top Categories');
    $url = Mage::getUrl('catalog/category/top');

    $helper->addFeedToHeader($url, $title);
  }

  public function showProductsWithoutSmallImagesOnly ($observer) {
    $param = Mage::app()
                ->getRequest()
                ->getParam('without_images_only');

    if ($param != 1)
      return;

    $collection = $observer->getCollection();

    $select = $collection->getSelect();
    $wherePart = $select->getPart(Zend_Db_Select::WHERE);

    foreach ($wherePart as $i => $condition)
      if (strpos($condition, 'image') !== false
          || strpos($condition, 'small_image') !== false
          || strpos($condition, 'thumbnail') !== false)
        unset($wherePart[$i]);

    $select->setPart(Zend_Db_Select::WHERE, $wherePart);

    $collection->addAttributeToFilter(
      array(
        array(
          'attribute' => 'image',
          'in' => array('no_selection', '')
        ),
        array(
          'attribute' => 'small_image',
          'in' => array('no_selection', '')
        ),
        array(
          'attribute' => 'thumbnail',
          'in' => array('no_selection', '')
        )
      )
    );
  }

  public function rememberAdminState($observer) {
    if (Mage::registry('is_admin_logged') !== null)
      return;

    Mage::unregister('is_admin_logged');
    Mage::register('is_admin_logged', false, true);

    $session = Mage::getSingleton('core/session');

    if ($session->getSessionSaveMethod() != 'files')
      return;

    $path = $session->getSessionSavePath()
            . DS
            . 'sess_'
            . Mage::getSingleton('core/cookie')
                ->get(Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE);

    if (!(file_exists($path) && ($data = file_get_contents($path))))
      return;

    $data = $this->_parseSessionData($data);

    if (!($data && isset($data['admin']['user'])))
      return;

    $user = $data['admin']['user'];

    Mage::unregister('is_admin_logged');
    Mage::register('is_admin_logged', $user->getId() > 0, true);
  }

  protected function _parseSessionData ($data) {
    $original = $_SESSION;

    if (!session_decode($data)) {
      $_SESSION = $original;

      return null;
    }

    $result = $_SESSION;
    $_SESSION = $original;

    return $result;
  }

  public function mergeAttributeOptions ($observer) {
    $attr = $observer->getAttribute();

    $code = $attr->getAttributeCode();
    $option = $attr->getData('option');

    if (!(isset($option['merge']['from']) && isset($option['merge']['to'])))
      return;

    $from = (int) $option['merge']['from'];
    $to = (int) $option['merge']['to'];

    $session = Mage::getSingleton('adminhtml/session');
    $helper = Mage::helper('productivity');

    if ($from == 0 || $to == 0 || $from == $to) {
      $msg = 'Selecting new or similar options for merging are not allowed. '
             . 'Please, select correct options';

      $session->addError($helper->__($msg));
      return;
    }

    $count = 0;

    try {
      $products = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToFilter($attr, array('finset' => $from));

      foreach ($products as $product) {
        $values = explode(',', $product->getData($code));

        if (!$values)
          continue;

        $values = array_flip($values);

        unset($values[$from]);
        $values[$to] = '';

        $product
          ->setData($code, implode(',', array_flip($values)))
          ->save();

        $count++;
      }
    } catch (Exception $e) {
      Mage::logException($e);

      $msg = 'Merging of attribute options failed. Please, check logs '
             . 'for additional info';

      $session->addError($helper->__($msg));

      return;
    }

    $option['delete'][$from] = true;

    $attr->setData('option', $option);

    $session->addSuccess($helper->__('Options were merged sucessfully.'));
    $session->addSuccess($helper->__('Number of updated products: ') . $count);
  }
}
