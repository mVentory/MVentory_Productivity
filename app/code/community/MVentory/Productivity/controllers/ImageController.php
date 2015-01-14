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
 * Image controller
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_ImageController
  extends Mage_Core_Controller_Front_Action {

  const ATTRIBUTE_CODE = 'media_gallery';

  public function preDispatch () {
    parent::preDispatch();

    Mage::getModel('productivity/observer')->rememberAdminState(null);
  }

  public function rotateAction () {
    $helper = Mage::helper('productivity');

    if (!$helper->isReviewerLogged())
      return $this->_error();

    $request = $this->getRequest();

    if (!$request->has('params'))
      return $this->_error();

    $params = $request->get('params');

    $angels = array('left' => 90, 'right' => -90);

    $hasRequiredValues = $params['file']
                         && array_key_exists($params['rotate'], $angels);

    if (!$hasRequiredValues)
      return $this->_error();

    if (!$product = $this->_getProduct($request->getParam('productId')))
      return $this->_error();

    //Export $file, $width, $height and $rotate variables
    extract($params);

    unset($params);

    try {
      // rotate image and get new file
      $newFileAbsolute = $helper->rotate($file, $angels[$rotate]);
    } catch (Exception $e) {
      Mage::logException($e);
      return $this->_error();
    }

    /* Checks if  is the main image*/
    $type = ($product->getImage() === $file)?'image':'thumbnail';    

    $result = array();

    try {
      //Update product image and get new base filename
      $result['file'] = $helper->updateImageInGallery(
        $file,
        $newFileAbsolute,
        $product,
        $type == 'image' ? array('image', 'small_image', 'thumbnail') : null,
        true,
        $type == 'image'
      );
    } catch (Exception $e) {
      Mage::logException($e);
      return $this->_error();
    }

    // get resized version of image
    $result['url'] = $this->_getImageUrl(
      $product,
      $type,
      $result['file'],
      $width ? $width : null,
      $height ? $height : null
    );

    $this->_success($result);
  }

  public function removeAction () {
    $helper = Mage::helper('productivity');

    if (!$helper->isReviewerLogged())
      return $this->_error();

    $request = $this->getRequest();

    if (!$request->has('params'))
      return $this->_error();

    $params = $request->get('params');

    if (!$params['file'])
      return $this->_error();

    if (!$product = $this->_getProduct($request->getParam('product')))
      return $this->_error();

    //Export $file, $width and $height variables
    extract($params);

    unset($params);

    try {
      $helper->remove($file, $product);
    } catch (Exception $e) {
      Mage::logException($e);
      return $this->_error();
    }

    $data = array();

    if($request->get('thumb') != 'true') {
      $data['url'] = $this->_getImageUrl(
        $product,
        'image',
        null,
        $width ? $width : null,
        $height ? $height : null
      );
    }

    $this->_success($data);
  }

  public function setmainAction () {
    $helper = Mage::helper('productivity');

    if (!$helper->isReviewerLogged())
      return $this->_error();

    $request = $this->getRequest();

    $hasRequiredParam = $request->has('params')
                        && $request->has('main_image_params');

    if (!$hasRequiredParam)
      return $this->_error();

    $thumb = $request->get('params');
    $image = $request->get('main_image_params');

    $hasRequiredValues = $thumb['file'] && $image['file'];

    if (!$hasRequiredValues)
      return $this->_error();

    if (!$product = $this->_getProduct($request->getParam('product')))
      return $this->_error();

    try {
      $helper->setMainImage($thumb['file'], $product);
    } catch (Exception $e) {
      Mage::logException($e);
      return $this->_error();
    }

    $result = array(
      'image' => array(
        'file' => $thumb['file'],
        'url' => $this->_getImageUrl(
          $product,
          'image',
          $thumb['file'],
          $image['width'] ? $image['width'] : null,
          $image['height'] ? $image['height'] : null
        )
      ),
      'thumbnail' => array(
        'file' => $image['file'],
        'url' => $this->_getImageUrl(
          $product,
          'thumbnail',
          $image['file'],
          $thumb['width'] ? $thumb['width'] : null,
          $thumb['height'] ? $thumb['height'] : null
        )
      )
    );

    $this->_success($result);
  }

  public function uploadAction () {
    $helper = Mage::helper('productivity');

    if (!$helper->isReviewerLogged())
      return $this->_error();

    if (!isset($_FILES['qqfile']))
      return $this->_error();

    if (!$product
          = $this->_getProduct($this->getRequest()->getParam('product_id')))
      return $this->_error();

    $uploader = new Mage_Core_Model_File_Uploader('qqfile');

    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
    $uploader->addValidateCallback(
      'catalog_product_image',
      Mage::helper('catalog/image'),
      'validateUploadFile'
    );
    $uploader->setAllowRenameFiles(true);
    $uploader->setFilesDispersion(true);

    $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')
                  ->getBaseTmpMediaPath()
              );

    Mage::dispatchEvent(
      'catalog_product_gallery_upload_image_after',
      array(
        'result' => $result,
        'action' => $this
      )
    );

    try {
      $file = $helper->add($product, $result);
    } catch (Exception $e) {
      Mage::logException($e);
      return $this->_error();
    }

    $this->_success(array('file' => $file));
  }

  private function _getProduct ($id) {
    if (($id = (int) $id) < 1)
      return;

    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    $product = Mage::getModel('catalog/product')->load($id);

    if (!$product->getId())
      return null;

    Mage::register('product', $product, true);

    return $product;
  }

  private function _getImageUrl ($product,
                                 $type = 'image',
                                 $file = null,
                                 $width = null,
                                 $height = null) {

    return Mage::helper('catalog/image')
      ->init($product, $type, $file)
      ->resize($width, $height)
      ->__toString();
  }

  private function _success ($data = null) {
    echo Mage::helper('core')->jsonEncode(array(
      'success' => true,
      'data' => $data
    ));
  }

  private function _error () {
    echo Mage::helper('core')->jsonEncode(array('success' => false));
  }
}
