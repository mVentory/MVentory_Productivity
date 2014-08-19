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
 * Various constants and values
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Model_Config {

  //Config paths
  const _CATEGORY_FLATTEN_TREE = 'productivity/category/flatten_tree';
  const _CATEGORY_HOME_URL = 'productivity/category/home_url';
  const _DISPLAY_PRODUCTS = 'productivity/category/display_descending_products';
  const _PRODUCT_SAVE_SCOPE = 'productivity/product/save_scope';
  const _PRODUCT_COPY_FIELDS = 'productivity/product/copy_fields';
  const _ANALYTICS_URL = 'productivity/analytics/url';

  //Product save scopes
  const PRODUCT_SCOPE_GLOBAL = 1;
  const PRODUCT_SCOPE_CURRENT = 2;

  const PRODUCT_FIELD_NAME = 'name';
  const PRODUCT_FIELD_DESCRIPTION = 'description';
}
