<?php
/**
 * Rewrite the PageController to check the admin login on store front
 *
 * @category   Mage_Cms_PageController
 * @package    Zetaprints_Productivity
 * @author     Zetaprints <anemets1@gmail.com>
 */
require_once("Mage/Cms/controllers/PageController.php");
class Zetaprints_Productivity_Cms_PageController extends Mage_Cms_PageController
{
	/*
	 * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in 
	 * registry 
	 */
	public function preDispatch () {
		Mage::getSingleton('core/session', array('name' => 'adminhtml'))
		  ->start();

		Mage::register('is_admin_logged',
					   Mage::getSingleton('admin/session')->isLoggedIn());

		parent::preDispatch();

		return $this;
	}
}
