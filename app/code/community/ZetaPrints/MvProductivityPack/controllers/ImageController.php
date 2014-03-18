<?php

class ZetaPrints_MvProductivityPack_ImageController
  extends Mage_Core_Controller_Front_Action {

  const ATTRIBUTE_CODE = 'media_gallery';

  public function preDispatch () {
    parent::preDispatch();

    Mage::getModel('MvProductivityPack/observer')->rememberAdminState(null);
  }

  public function rotateAction () {
    $helper = Mage::helper('MvProductivityPack');

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

    // rotate image and get new file
    $newFileAbsolute = $helper->rotate($file, $angels[$rotate]);

    $type = $request->get('thumb') == 'true'?'thumbnail':'image';

    $result = array();

    if($type == 'image') {
      // update main product image and get new base filename
      $result['file']
        = $helper
            ->updateImageInGallery($file,
                                   $newFileAbsolute,
                                   $product,
                                   array('image', 'small_image', 'thumbnail'));
    } else {
      $result['file'] = $helper
                ->updateImageInGallery($file, $newFileAbsolute, $product);
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
    $helper = Mage::helper('MvProductivityPack');

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

    $helper->remove($file, $product);

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
    $helper = Mage::helper('MvProductivityPack');

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

    $helper->setMainImage($thumb['file'], $product);

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
    $helper = Mage::helper('MvProductivityPack');

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

    if (!$file = $helper->add($product, $result))
      return $this->_error();

    $this->_success(array('file' => $file));
  }

  private function _getProduct ($id) {
    if (($id = (int) $id) < 1)
      return;

    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    $product = Mage::getModel('catalog/product')->load($id);

    return $product->getId() ? $product : null;
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
