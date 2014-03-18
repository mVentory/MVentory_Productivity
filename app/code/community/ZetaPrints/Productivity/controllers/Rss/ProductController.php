<?php

class ZetaPrints_Productivity_Rss_ProductController
  extends Mage_Core_Controller_Front_Action {

  public function latestAction () {
    $this
      ->getResponse()
      ->setHeader('Content-type', 'application/rss+xml; charset=UTF-8');

    $this->loadLayout(false);
    $this->renderLayout();
  }
}
