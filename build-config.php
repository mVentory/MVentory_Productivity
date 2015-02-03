<?php

$version = '1.1.0';

$notes = <<<EOT
* Add setting to limit the list of fields available for editing via the frontend
* Add support for editing of configurable products
* Add tabs for quick generating of QR labels (works only when MVentory_API extesion is installed)
* Extend filtering by all image types (i.e. base image, small image and thumbnail) in the list of products with missing images
* Allow to expand categories with &apos;Include in Navigation Menu&apos; set to No
EOT;

$description = <<<EOT
A front-end toolbar for admins and nominated customers to perform site management via the front end:

The extension provides simple admin functionality to a group of site admins or power users without them accessing the admin interface as often or not at all.

The shortcuts allow quick navigation between front-end and admin interfaces. E.g. if you are looking at a product details page and need to make changes to it via the admin you can jump there in one click instead of searching for the product in the admin.

The same applies to category and CMS pages.

* Edit product details, price, attributes, etc.
* Upload product images
* Set main product image
* Delete, rotate product images
* Shortcuts to the admin interface for the page
* Easily find products with missing images
* Shortcut to Google Analytics

Theme compatibility

Most well designed themes are compatible with the extension. You may need to make a few minor changes. See documentation on GitHub.

Enable / Disable the Toolbar

1. Log in to the admin interface.

2. Come back to the front end.

3. You should see the toolbar at the top.


Customers assigned to customer group called &quot;Reviewers&quot; can see most of the functions. They need to log in as customers via the front end.

Source code and documentation

https://github.com/mVentory/MVentory_Productivity

Support

Bug reports are welcome at support@zetaprints.com

License

The extension is free open source released under OSL 3 license. Please, contribute your code if you make useful changes.
EOT;

$summary = <<<EOT
Edit products details, upload and manage photos via front end or use shortcuts directly to admin pages.
EOT;

return array(

//The base_dir and archive_file path are combined to point to your tar archive
//The basic idea is a seperate process builds the tar file, then this finds it
//'base_dir'               => '/home/bitnami/build',
//'archive_files'          => 'tm.tar',

//The Magento Connect extension name.  Must be unique on Magento Connect
//Has no relation to your code module name.  Will be the Connect extension name
'extension_name'         => 'MVentory_Productivity',

//Your extension version.  By default, if you're creating an extension from a 
//single Magento module, the tar-to-connect script will look to make sure this
//matches the module version.  You can skip this check by setting the 
//skip_version_compare value to true
'extension_version'      => $version,
'skip_version_compare'   => true,

//You can also have the package script use the version in the module you 
//are packaging with. 
'auto_detect_version'   => false,

//Where on your local system you'd like to build the files to
//'path_output'            => '/home/bitnami/build/packages/MVentory_TradeMe',

//Magento Connect license value. 
'stability'              => 'stable',

//Magento Connect license value 
'license'                => 'Open Software License version 3.0',

//Magento Connect license URI 
'license_uri'            => 'http://opensource.org/licenses/osl-3.0.php',

//Magento Connect channel value.  This should almost always (always?) be community
'channel'                => 'community',

//Magento Connect information fields.
'summary'                => $summary,
'description'            => $description,
'notes'                  => $notes,

//Magento Connect author information. If author_email is foo@example.com, script will
//prompt you for the correct name.  Should match your http://www.magentocommerce.com/
//login email address
'author_name'            => 'Anatoly A. Kazantsev',
'author_user'            => 'anatoly',
'author_email'           => 'anatoly@mventory.com',
/*
// Optional: adds additional author nodes to package.xml
'additional_authors'     => array(
  array(
    'author_name'        => 'Mike West',
    'author_user'        => 'micwest',
    'author_email'       => 'foo2@example.com',
  ),
  array(
    'author_name'        => 'Reggie Gabriel',
    'author_user'        => 'rgabriel',
    'author_email'       => 'foo3@example.com',
  ),
),
*/
//PHP min/max fields for Connect.  I don't know if anyone uses these, but you should
//probably check that they're accurate
'php_min'                => '5.2.0',
'php_max'                => '6.0.0',

//PHP extension dependencies. An array containing one or more of either:
//  - a single string (the name of the extension dependency); use this if the
//    extension version does not matter
//  - an associative array with 'name', 'min', and 'max' keys which correspond
//    to the extension's name and min/max required versions
//Example:
//    array('json', array('name' => 'mongo', 'min' => '1.3.0', 'max' => '1.4.0'))
'extensions'             => array(),
'packages'               => array(array('name' => 'MVentory_API', 'channel' => 'community'))
);
