<?php

class ZetaPrints_MvProductivityPack_Model_Observer {
  public function addCategoryFeed ($observer) {
    $helper = Mage::helper('core/url');

    $url = $helper->getCurrentUrl();
    $url = $helper->addRequestParam($url, array('rss' => 1));

    Mage::helper('MvProductivityPack/rss')->addFeedToHeader($url);
  }

  public function addLatestProductsFeed ($observer) {
    $helper = Mage::helper('MvProductivityPack/rss');

    $title = $helper->__('Latest products');
    $url = Mage::getUrl('mvproductivitypackf/rss_product/latest');

    $helper->addFeedToHeader($url, $title);
  }

  public function addTopCategoriesFeed ($observer) {
    $helper = Mage::helper('MvProductivityPack/rss');

    $title = $helper->__('Top Categories');
    $url = Mage::getUrl('catalog/category/top');

    $helper->addFeedToHeader($url, $title);
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

}
