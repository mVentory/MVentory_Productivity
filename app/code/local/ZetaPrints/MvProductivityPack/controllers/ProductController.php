<?php
/**
 * Rewrite the ProductController to check the admin login on store front
 *
 * @category   Mage_Catalog_CategoryController
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */
require_once("Mage/Catalog/controllers/ProductController.php");
class ZetaPrints_MvProductivityPack_ProductController extends Mage_Catalog_ProductController
{
	/**
	 * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in 
	 * registry 
	 */
	public function preDispatch () {
		Mage::helper('MvProductivityPack')->saveAdminState();

		return parent::preDispatch();

		return $this;
	}
}
