<?php

class ZetaPrints_MvProductivityPack_FeedController
  extends Mage_Core_Controller_Front_Action {

  public function updateAction () {
    $request = $this->getRequest();

    if (!$url = $request->getParam('url'))
      return;

    Mage::app()->cleanCache(md5($url));

    $subject = 'WP page was updated';
    $body = 'URL: ' . $url;

    Mage::helper('MvProductivityPack')->sendEmail($subject, $body);
  }
}
