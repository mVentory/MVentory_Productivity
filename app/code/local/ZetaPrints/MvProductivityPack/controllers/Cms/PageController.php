<?php
/**
 * Rewrite the PageController to check the admin login on store front
 *
 * @category   Mage_Cms_PageController
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */
require_once("Mage/Cms/controllers/PageController.php");
class ZetaPrints_MvProductivityPack_Cms_PageController extends Mage_Cms_PageController
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
