<?php

class ZetaPrints_MvProductivityPack_ImageController
  extends Mage_Core_Controller_Front_Action {

  public function preDispatch () {
    Mage::getSingleton('core/session', array('name' => 'adminhtml'))
      ->start();

    Mage::register('is_admin_logged',
                   Mage::getSingleton('admin/session')->isLoggedIn());

    parent::preDispatch();

    return $this;
  }

  protected function _isAdmin () {
    return Mage::registry('is_admin_logged') === true;
  }

  public function getAction () {
    $request = $this->getRequest();

    if (!$request->has('file'))
        return;

    $fileName = $request->get('file');

    $dispretionPath
                  = Mage_Core_Model_File_Uploader::getDispretionPath($fileName);

    $fileName = $dispretionPath . DS . $fileName;

    $media = Mage::getModel('catalog/product_media_config');

    if (!file_exists($media->getMediaPath($fileName)))
      return;

    $tokens = explode('.', $fileName);

    $type = $tokens[count($tokens) - 1];

    if ($type == 'jpg')
      $type = 'jpeg';

    $width = $request->has('width') && is_numeric($request->get('width'))
               ? (int) $request->get('width') : null;

    $height = $request->has('height') && is_numeric($request->get('height'))
                  ? (int) $request->get('height') : null;

    if ($width || $height) {
      $image = Mage::getModel('catalog/product_image');

      $image
        ->setBaseFile($fileName)
        ->setKeepFrame(false)
        ->setWidth($width)
        ->setHeight($height)
        ->resize()
        ->saveFile();

      $fileName = $image->getNewFile();
    }

    $this
      ->getResponse()
      ->setHeader('Pragma', '', true)
      ->setHeader('Expires', '', true)
      ->setHeader('Content-Type', 'image/' . $type , true)
      ->setHeader('Content-Length', filesize($fileName), true)
      ->setBody(file_get_contents($fileName));
  }

  public function rotateAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('file') && $request->has('rotate')))
      return;

    $file = $request->get('file');
    $rotate = $request->get('rotate');

    $angels = array('left' => 90, 'right' => -90);

    if (!($file && $rotate && array_key_exists($rotate, $angels)))
      return;

    Mage::helper('MvProductivityPack')
      ->rotate($file, $angels[$rotate]);

    return Zend_Json::encode(true);
  }

  public function removeAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('file') && $request->has('product')))
      return;

    $file = $request->get('file');
    $productId = (int) $request->get('product');

    if (!($file && $productId >= 0))
      return;

    Mage::helper('MvProductivityPack')
      ->remove($file, $productId);

    return Zend_Json::encode(true);
  }

  public function setmainAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('file') && $request->has('product')))
      return;

    $file = $request->get('file');
    $productId = (int) $request->get('product');

    if (!($file && $productId >= 0))
      return;

    Mage::helper('MvProductivityPack')
      ->setMainImage($file, $productId);

    return Zend_Json::encode(true);
  }
}
