# Productivity

The Productivity extension contains different features which make life of store administrator easier. It includes fast cross-switching between products, categories, CMS pages and respective editors, basic editing of product and its images, uploading new images to product in the frontend.

## Frontend features

### Productivity panel
The panel is shown on _Category_, _Product_ and _CMS_ pages in the front end. It contains various buttons such as:

* Button for fast editing pages in the admin. It opens a corresponding page in the admin if the user is logged in as an admin to the same domain.
* Button for basic editing of product in the front-end
* Button for uploading new images for product
* Button for fast opening analytics page
* Help button

### Simple image editor
Pop-up panel which is shown when the mouse pointer is over product image. It allows to delete, rotate and make main images.

The editor is enabled by default but only configured for the default theme. Custom themes with modified HTML layout or sizes of product images require additional configuration.

The editor is configured via theme's layout files such as local.xml. Place following code in that file to adjust it for the custom theme:

    <catalog_product_view>
      <reference name="productivity.image.edit">
        <action method="setTemplate">
          <template>custom/template_for/image_edit.phtml</template>
        </action>

        <action method="setImageWrapperSelector">
          <selector><![CDATA[#product_addtocart_form .product-image]]></selector>
        </action>
        <action method="setImageSelector">
          <selector><![CDATA[img]]></selector>
        </action>

        <action method="setThumbWrapperSelector">
          <selector><![CDATA[#product_addtocart_form .more-views li]]></selector>
        </action>
        <action method="setThumbSelector">
          <selector><![CDATA[a img]]></selector>
        </action>

        <action method="setImageSize">
          <width>265</width>
          <height></height>
        </action>

        <action method="setThumbSize">
          <width>56</width>
          <height></height>
        </action>
      </reference>
    </catalog_product_view>

### RSS import

Allow to import content (content:encoded tag) of first item from feed. Add 'productivity/rss_import' block to the layout in necessary place to show content of the item.

Using example:


	<reference name="insert_to_block">
	  <block type="productivity/rss_import" name="productivity.rss.import" after="some_block" before="some_black">
		
		<!-- Set url to feed -->
		<action method="setUri"><uri>http://foo.bar/feed/</uri></action>
		
		<!-- Set value of ID attribute -->
		<action method="setElementId"><id>my-unique-id</id></action>

		<!-- Add additional CSS classes -->
		<action method="setAdditionalClass"><class>my-class another-class</class></action>

		<!-- Set cache lifetime in seconds or ... -->
		<action method="setCacheLifetime"><period>3600</period></action>

		<!-- ... Disable caching -->
		<action method="unsCacheLifetime" />

	  </block>
	</reference>


## Backend features

  * "View in frontend" button appears on Product, Category and other admin pages above the main block of buttons to quickly jump from the admin to the front end page

Adds button for preview product, category and cms page
  - Added in adminhtml's productivity.xml:

  <adminhtml_catalog_product_edit>
    <reference name="head">
      <action method="addCss"><name>productivity.css</name></action>
    </reference>
    <reference name="content">
      <block name="product.edit.frontview.button" type="productivity/adminhtml_catalog_product_edit_button" before="-" template="productivity/catalog/product/edit/button.phtml" />
    </reference>
  </adminhtml_catalog_product_edit>
  <adminhtml_catalog_category_edit>
    <reference name="head">
      <action method="addCss"><name>productivity.css</name></action>
    </reference>
    <reference name="content">
      <block name="category.edit.frontview.button" type="productivity/adminhtml_catalog_category_edit_button" before="-" template="productivity/catalog/category/edit/button.phtml" />
    </reference>
  </adminhtml_catalog_category_edit>
  <adminhtml_cms_page_edit>
    <reference name="head">
      <action method="addCss"><name>productivity.css</name></action>
    </reference>
    <reference name="content">
      <block name="page.edit.frontview.button" type="productivity/adminhtml_cms_page_edit_button" before="-" template="productivity/cms/page/edit/button.phtml" />
    </reference>
  </adminhtml_cms_page_edit>


Adds middle click for Attributes
Adds middle click for Attribute Sets
  - Done by js code in update_grid.js file which is appended to the page in
    adminhtml's productivity.xml:

  <adminhtml_catalog_product_attribute_index>
    <reference name="head">
      <action method="addJs"><script>productivity/adminhtml/update_grid.js</script></action>
      <action method="addCss"><name>productivity.css</name></action>
    </reference>
  </adminhtml_catalog_product_attribute_index>
  <adminhtml_catalog_product_set_index>
    <reference name="head">
      <action method="addJs"><script>productivity/adminhtml/update_grid.js</script></action>
    </reference>
  </adminhtml_catalog_product_set_index>

Adds middle click for attributes in editing attribute set
  - Done by js code in script.js.phtml file which is appended to the page in
    adminhtml's productivity.xml:

  <adminhtml_catalog_product_set_edit>
    <reference name="js">
      <block type="productivity/adminhtml_catalog_product_attribute_set_edit_script" template="productivity/catalog/product/attribute/set/edit/script.js.phtml" name="attribute.script.js" />
    </reference>
  </adminhtml_catalog_product_set_edit>


# RSS feeds

You can request some magento data in a form of an rss feed. Currently we have only 2 feeds:

*1. List of products per category.*

Example url: http://offsider.co.nz/building-renovation.html?dw_panel_type_=84&price=100-200&rss=1&thumbnail_size=300x200&fullimage_size=400x500

Meaning of the parameters:
  * rss - Needs to be set to "1" to make the server return the feed instead of the regular list of products
  * thumbnail_size - Size of thumbnail images returned in the feed. This needs to comply with our list of available dimensions (see mventory/Product Images CDN/Resizing Dimensions)
  * fullimage_size - Works the same way as "thumbnail_size" but sets the dimensions of full size images in the feed.

Please note that if thumbnail_size and/or fullimage_size are not specified they will be set to hardcoded default values:
  * "thumbnail_size" default value: "215x170"
  * "fullimage_size" default value: "300x300"

*2. List of top categories per store.*

Example url: http://offsider.co.nz/index.php/catalog/category/top

# Slideshow widget

*Widget type:* `productivity/slideshow`

This widget is available in CMS pages to output a list with product info for inclusion in a slideshow. Wrap the widget into arbitrary HTML, add scripts via the layout XML and the page gets a slideshow of your choosing without digging into magento code.

*Params:*
  * `products_count` - how many products to return in the list
  * `item_template` - escaped HTML with variables
  * `image_size` - from the list of available sizes and no resizing is done

*Variables:*
  * `%url%` - absolute URL of the product
  * `%img%` - absolute URL of the product image
  * `%name%` - product name
  * `%price%` - product price
  * `%price-block%` - block with product price as it's showed in category view
  * `%if:sale% .. <content> .. %end:sale%` - shows its content if product has sale price at the moment

As of now it outputs a random list of products in stock.

### Widget example


	{{widget type="productivity/slideshow" products_count="40" item_template="<li>
	  <a href=\"%url%\">
		<img src=\"%img%\" alt=\"%name% %price%\"/>
	  </a>
	
	  %if:sale%<span>(Sale!)</span>%end:sale%
	</li>" image_size="215x170"}}


### Full example

*Content: *


	<div id="ri-grid" class="ri-grid ri-shadow">{{widget type="productivity/slideshow" products_count="40" item_template="<li><a href=\"%url%\"><img src=\"%img%\" alt=\"%name% %price%\"/></a></li>" image_size="215x170"}}</div>
	<script type="text/javascript">// <![CDATA[
	jQuery(function ($) {
	  $('#ri-grid').gridrotator();
	});
	// ]]></script>


*Layout XML:*


	<reference name="head">
	  <action method="addItem"><type>js_css</type><name>gridrotator/gridrotator.css</name></action>
	  <action method="addJs"><script>gridrotator/jquery-gridrotator.js</script></action>
	</reference>


# Attribute values widget

*Widget type:* `productivity/widget_attribute`

This widget is available in CMS pages to output a list with attribute values for inclusion in a slideshow. Wrap the widget into arbitrary HTML, add scripts via the layout XML and the page gets a slideshow of your choosing without digging into magento code.

*Params:*
  * `item_template` - escaped HTML with variables
  * `code` - attribute's code

*Variables:*
  * `%code%` - attribute's code
  * `%label%` - label of attribute's value
  * `%value%` - id of attribute's value

### Widget example


	{{widget type="productivity/widget_attribute" item_template="<li>%code%: %label% (%value%)</li>" code="brands"}}


### Full example

*Content: *


	{{widget type="productivity/widget_attribute" item_template="<a href=\"/fashion/clothing.html?%code%=%value%\">%label%</a> " code="cloth_brand_"}}


# Related products block

*Block type:* `productivity/product_related`

This block is used to show related products based on a shared attribute and its value in the current product. It can be used only on a product details page.

The block returns a list of all products that have a matching value of the same attribute regardless on the attribute set or category. E.g. there are cups, mugs and plates who share attribute `color`. If the product details page displays a `purple` mug then the related products will have other `purple` mugs, cups and plates.

*Params:*
  * `attribute_code` - attribute's code
  * `product_count` - number of products to show

### Block example


	<block type="productivity/product_related" name="product.info.related" as="related_products" template="catalog/product/list/related.phtml">
	  <action method="setAttributeCode">
		<attribute_code>color</attribute_code>
	  </action>

	  <action method="setProductsCount">
		<products_count>5</products_count>
	  </action>
	</block>


### Full example

*Layout file* (for example local.xml):


	<block type="productivity/product_related" name="product.info.related" as="related_products" template="catalog/product/list/related.phtml">
	  <action method="setAttributeCode">
		<attribute_code>color</attribute_code>
	  </action>

	  <action method="setProductsCount">
		<products_count>5</products_count>
	  </action>
	</block>


*Basic template which can be used in the block:*


	<?php $_products = $this->getProductCollection(); ?>

	<?php if ($_products->getSize()): ?>

	<?php

	  $_helper = $this->helper('catalog/output');
	  $_imageHelper = $this->helper('catalog/image');

	  $_addToCartText = $this->__('Buy now');

	?>

	<ul>
	  <?php foreach ($_products as $_product): ?>

	  <?php

		$_name = $_helper->productAttribute($_product, $_product->getName(), 'name');
		$_productUrl = $_product->getProductUrl();
	
	  ?>

	  <li>
		<a href="<?php echo $_productUrl; ?>" title="<?php echo $_name; ?>">
		  <img src="<?php echo $_imageHelper->init($_product, 'small_image')->resize(170); ?>" width="170" alt="<?php echo $this->htmlEscape($this->getImageLabel($_product, 'small_image')); ?>" />
		</a>

		<h2 class="product-name"><?php echo $_name; ?></h2>

		<?php echo $this->getPriceHtml($_product, true); ?>

		<button type="button" title="<?php echo $_addToCartText; ?>" class="button btn-cart" onclick="setLocation('<?php echo $_productUrl; ?>')">
		  <span>
			<span><?php echo $_addToCartText; ?></span>
		  </span>
		</button>
	  </li>
	
	  <?php endforeach ?>
	</ul>

	<?php endif; ?>


*Inserting block as a child in some template to output related products* (for example catalog/product/view.php):


	<?php echo $this->getChildHtml('related_products'); ?>
