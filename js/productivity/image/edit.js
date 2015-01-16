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
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

(function (productivity, $) {

productivity.image = {};
productivity.edit = {};

function __ (s) {
  return window.Translator.translate(s);
}

function confirmRemove ($el) {
  var answer;

  $el.addClass('productivity-state-warning');
  answer = confirm(__('Do you want to delete this image?'));
  $el.removeClass('productivity-state-warning');

  return answer;
}



$(function () {

  var $form = $('#product_addtocart_form');
  var $panel = $('#productivity-image-edit-panel');  

  $panel
    .on('mouseenter', function () {
      $panel.show();
    })
    .on('mouseleave', panel_mouseleave_handler);

  $panel
    .children('.rotate-image')
    .filter('.rotate-left')
      .on('click', { rotate: 'left' }, rotate_button_click_handler)
    .end()
    .filter('.rotate-right')
      .on('click', { rotate: 'right' }, rotate_button_click_handler);

  $panel
    .children('.remove-image')
    .on('click', remove_button_click_handler);

  $panel
    .children('.set-main-image')
    .on('click', set_main_button_click_handler);

  function add_panel ($element, data) {
    $element.on(
      {
        mouseenter: image_mouseenter_handler,
        mouseleave: panel_mouseleave_handler,
      },
      data
    );
  }

  productivity.func = { add_panel: add_panel };

  function get_panel_position ($element) {
    var position = $element.offset();

    position.top += $element.height() - 10;
    position.left += 10;

    return position;
  }

  function get_element ($element, type) {
    if (!productivity.edit.wrapper[type])
      return $element;

    $element = $element.find(productivity.edit.selector[type]);

    if (!$element.length)
      return;

    return $element;
  }

  function show_loader ($element, type) {
    if (type != 'image')
      return;

    var $loader = $('<div class="image-editor-loader" />')
      .appendTo($element.parent())
      .show();

    return function () {
      $loader.remove();
    }
  }

  function on_error ($element, data) {
     var currentBorder = $element.css('border');
      $element.css('border','1px solid red');
      setTimeout(function(){$element.css('border',currentBorder)}, 5000);
      alert('Product ID: ' + data.product_id + '\nImage file: ' + data.file + '\nStatus: ' + data.status);
  }

  function on_remove ($element, data) {
    if (data.url)
      $element.prop('src', data.url);
    else
      $element.remove();
  }

  function on_rotate ($element, data) {
    $element.prop('src', data.url);

    //!!!FIX: also return link to full size image
    //$element.parent('a').prop('href', '/media/catalog/product' + data.base);

    $element.data('productivity').file = data.file;
  }

  function on_setmain ($element, data) {
    $element.image.prop('src', data.image.url);
    $element.thumb.prop('src', data.thumbnail.url);

    $element.image.data('productivity').file = data.image.file;
    $element.thumb.data('productivity').file = data.thumbnail.file;
  }

  function rotate_image ($img, params, type, cb) {
    var productId = $form.find('input[name="product"]').val();

    $.ajax({
      url: productivity.image.url.rotate,
      type: 'POST',
      dataType: 'json',
      data: {
        params: params,        
        productId: productId
      },
      error: function (jqXHR, status, errorThrown) {
        var data = {
          product_id: productId,
          file: params.file,
          status: status
        };

        cb.on_error($img, data);
      },
      success: function (data, status, jqXHR) {
        if (data.success)
          cb.on_success($img, data.data)
        else {
          var data = {
            product_id: productId,
            file: params.file,
            status: status
          };

          cb.on_error($img, data);
        }
      },
      complete: cb.on_complete
    });
  }

  function remove_image ($img, params, product_id, type, cb) {
    var thumb = type == 'thumbnail';

    $.ajax({
      url: productivity.image.url.remove,
      type: 'POST',
      dataType: 'json',
      data: { params: params, product: product_id, thumb: thumb },
      error: function (jqXHR, status, errorThrown) {
        var data = {
          product_id: product_id,
          file: params.file,
          status: status
        };

        cb.on_error($img, data);
      },
      success: function (data, status, jqXHR) {
        if (data.success)
          cb.on_success($img, data.data)
        else {
          var data = {
            product_id: product_id,
            file: params.file,
            status: status
          };

          cb.on_error($img, data);
        }
      },
      complete: cb.on_complete
    });
  }

  function set_main_image ($image,
                           $thumb,
                           params,
                           main_image_params,
                           product_id,
                           cb) {

    $.ajax({
      url: productivity.image.url.setmain,
      type: 'POST',
      dataType: 'json',
      data: { product: product_id,
              params: params,
              main_image_params: main_image_params },
      error: function (jqXHR, status, errorThrown) {
        var data = {
          product_id: product_id,
          file: params.file,
          status: status
        };

        cb.on_error($image, data);
      },
      success: function (data, status, jqXHR) {
        if (data.success)
          cb.on_success({ image: $image, thumb: $thumb }, data.data)
        else {
          var data = {
            product_id: product_id,
            file: params.file,
            status: status
          };

          cb.on_error($image, data);
        }
      },
      complete: cb.on_complete
    });
  }

  function image_mouseenter_handler (event) {
    if($panel.hasClass('disabled'))
      return;

    var $this = $(this);

    $panel.removeClass('productivity-scope-uploader productivity-scope-images');

    var data = (typeof event.data === 'function') ? event.data() : event.data;

    if (data.image.type == 'image')
      $panel.addClass('productivity-state-main-image');
    else
      $panel.removeClass('productivity-state-main-image');

    if (typeof data.panel.position === 'function')
      css = data.panel.position($this)
    else {
      css = $this.offset();

      css.top += data.panel.position.top;
      css.left += data.panel.position.left;
    }

    if (typeof data.panel.size === 'function') {
      var size = data.panel.size($this);

      css.width = size.width;
      css.height = size.height;
    }

    //Store elements for image and its wrapper
    data.wrapper = { $: $this };
    data.element = {
      $: typeof data.panel.element === 'function'
          ? data.panel.element($this, data.image.type)
            : $this
    };

    $panel
      .removeAttr('style')
      .css(css)
      .addClass('productivity-scope-' + event.data.panel.scope)
      .show()
      .data(data);
  }

  function panel_mouseleave_handler () {
    $panel.hide();
  }

  function rotate_button_click_handler (event) {
    event.preventDefault();

    var data = $panel.data();

    $panel
      .addClass('disabled')
      .hide();

    data.element.$.css('opacity', '0.5');

    if (typeof data.panel.loader === 'function')
      var hide_loader = data.panel.loader(data.element.$, data.image.type)

    var image = data.element.$.data('productivity');

    if (!image) {
      image = {
        file: data.image.file
                ? data.image.file
                  : get_filename_from_url(data.element.$.prop('src')),
      };
    }

    data.element.$.data('productivity', image);

    var params = {
      file: image.file,
      width: data.image.width === undefined
               ? productivity.edit.size[data.image.type].width
                 : data.image.width,
      height: data.image.height === undefined
               ? productivity.edit.size[data.image.type].height
                 : data.image.height,
      rotate: event.data.rotate
    };

    data.panel.action.rotate.on_complete = function () {
      if (hide_loader)
        hide_loader();

      data.element.$.css('opacity', '1')

      $panel.removeClass('disabled');
    };

    rotate_image(data.element.$, params, data.image.type, data.panel.action.rotate);

    return false;
  }

  function remove_button_click_handler (event) {
    event.preventDefault();

    var data = $panel.data();

    data.element.$.css('opacity', '0.5');

    if (!confirmRemove(data.wrapper.$)) {
      data.element.$.css('opacity', '1');

      return false;
    }

    var image = data.element.$.data('productivity');

    if (!image) {
      image = {
        file: data.image.file
                ? data.image.file
                  : get_filename_from_url(data.element.$.prop('src')),
      };
    }

    data.element.$.data('productivity', image);

    var params = {
      file: image.file,
      width: productivity.edit.size[data.image.type].width,
      height: productivity.edit.size[data.image.type].height
    };

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    data.panel.action.remove.on_complete = function () {
      data.element.$.css('opacity', '1');
      $panel.hide();
    };

    remove_image(data.element.$, params, product_id, data.image.type, data.panel.action.remove);

    return false;
  }


  function set_main_button_click_handler (event) {
    event.preventDefault();

    var data = $panel.data();

    if (data.image.type != 'thumbnail')
      return false;

    $panel
      .addClass('disabled')
      .hide();

    if (typeof data.panel.loader === 'function')
      var hide_loader = data.panel.loader(data.element.$, data.image.type)

    var $this = $(this);

    /*$this.off('click', set_main_button_click_handler);*/

    var thumb = data.element.$.data('productivity');

    if (!thumb) {
      thumb = {
        file: data.image.file
                ? data.image.file
                  : get_filename_from_url(data.element.$.prop('src')),
      };
    }

    data.element.$.data('productivity', thumb);

    var params = {
      file: thumb.file,
      width: data.image.width === undefined
               ? productivity.edit.size.thumbnail.width
                 : data.image.width,
      height: data.image.height === undefined
               ? productivity.edit.size.thumbnail.height
                 : data.image.height
    };

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    var $mainImage = productivity.edit.wrapper.image
                       ? $(productivity.edit.wrapper.image)
                           .find(productivity.edit.selector.image)
                         : $(productivity.edit.selector.image);
    // image-main
    var image = $mainImage.data('productivity');
    
    if (!image) {
        /* Main image fix for theme rwd */
       if ($mainImage.prop('src') == undefined){
          $mainImage = $("#image-main");
       }        
      image = {
        file: get_filename_from_url($mainImage.prop('src')),
      };
    }

    $mainImage.data('productivity', image);

    var main_image_params = {
      file: image.file,
      width: productivity.edit.size.image.width,
      height: productivity.edit.size.image.height
    };

    $mainImage.css('opacity', '0.5');
    data.element.$.css('opacity', '0.5');

    $mainImage.parent().append('<div class="image-editor-loader"></div>');
    $('.image-editor-loader').show();

    data.panel.action.setmain.on_complete = function () {
      var link = data.element.$.parent('a').prop('href');
      data.element.$.parent('a').prop('href', $mainImage.parent('a').prop('href'));
      $mainImage.parent('a').prop('href', link);

      if (hide_loader)
        hide_loader();

      data.element.$.css('opacity', '1');
      $mainImage.css('opacity', '1');

      $panel.removeClass('disabled');
    };

    set_main_image(
      $mainImage,
      data.element.$,
      params,
      main_image_params,
      product_id,
      data.panel.action.setmain
    );
  }

  function get_filename_from_url (url) {
    return url.substring(
      url.lastIndexOf('/', url.lastIndexOf('/', url.lastIndexOf('/') - 1) - 1)
    );
  }
});

}(window.productivity = window.productivity || {}, jQuery));
