<?php
/**
 * Rewrite the IndexController to check the admin login on store front
 *
 * @category   Mage_Cms_IndexController
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */
require_once("Mage/Cms/controllers/IndexController.php");
class ZetaPrints_MvProductivityPack_Cms_IndexController extends Mage_Cms_IndexController
{
	/**
	 * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in 
	 * registry 
	 */
	public function preDispatch () {
		Mage::helper('MvProductivityPack')->saveAdminState();

		parent::preDispatch();

		return $this;
	}
}
