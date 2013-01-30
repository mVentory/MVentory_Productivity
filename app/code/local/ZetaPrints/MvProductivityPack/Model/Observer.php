<?php

class ZetaPrints_MvProductivityPack_Model_Observer {
  public function addCategoryFeed ($observer) {
    $head = Mage::app()
              ->getFrontController()
              ->getAction()
              ->getLayout()
              ->getBlock('head');

    if (!$head)
      return;

    $helper = Mage::helper('core/url');

    $url = $helper->getCurrentUrl();
    $url = $helper->addRequestParam($url, array('rss' => 1));

    $head->addItem('rss', $url, 'title="' . $head->getTitle() . '"');
  }
}
