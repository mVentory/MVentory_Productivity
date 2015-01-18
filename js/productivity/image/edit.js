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

  /* Set productivity panel in all images*/
  $('#productivity-uploader-previews').children().each(function( index, value ){            
      set_panel($(this), $(this).attr('image'), $(this).attr('type'));
  });

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

  function show_loader ($element, type) {

    var $loader = $('<div class="image-editor-loader" />')
      .appendTo($element)
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
          cb.on_success($img, data.data, params)
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
          cb.on_success($img, data.data, params)
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
          cb.on_success({ image: $image, thumb: $thumb }, data.data, $thumb)
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
      .addClass('productivity-scope-uploader')
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
    
    var image = data.image;

    if (!image) {
      //TODO: What if there is no image
      return false;
    }

    data.element.$.data('productivity', image);

    var params = {
      file: image.file,
      sizes: get_images_sizes(image.file),
      rotate: event.data.rotate
    };

    data.panel.action.rotate.on_success = function ($element, data, params) {
      $element.css('background-image', 'url(' + data.url + ')');
      $element.data('productivity').file = data.file;
      images = get_images_from_url(params.file);
      images.each(function( index, image ){        
        $(image).prop('src', data[index]);
      
     });
      
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

    var image = data.image;

    if (!image) {
      //TODO: What if there is no image
      return false;
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

    data.panel.action.remove.on_success = function ($element, data, params) {
      $element.remove();      
      images = get_images_from_url(params.file);
      images.each(function( index, image ){        
        $(image).remove();
      
     });
    };

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

    $panel.addClass('disabled').hide();

    if (typeof data.panel.loader === 'function')
      var hide_loader = data.panel.loader(data.element.$, data.image.type)

    var $this = $(this);    

    var thumb = data.image;

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

    var product_id = $form.find('input[name="product"]').val();

    /* Find base image url */
    var mainImageUrl = $("#productivity-panel").find("[type='image']").attr('src');  

    if (mainImageUrl === undefined) {
      mainImageUrl =$(get_images_from_url('placeholder/image.jpg')).first().attr('src');
    };       

    var $mainImage = $(get_images_from_url(mainImageUrl)).first();    
    
    // Get main image url
    var imageUrl = $mainImage.first().attr('src');      

    $mainImage.data('productivity', imageUrl);

    var main_image_params = {
      file: imageUrl,
      width: $mainImage.width(),
      height: $mainImage.height()
    };

    $mainImage.css('opacity', '0.5');
    data.element.$.css('opacity', '0.5');
    
    $('.image-editor-loader').show();

    data.panel.action.setmain.on_success = function ($element, data, thumb) {
      $element.image.prop('src', data.image.url);

      /* Sets all images to type "thumbnail"*/
      $('#productivity-uploader-previews').children().each(function( index, value ){
        $(value).attr('type', 'thumbnail');
      });

      /* Sets image to type "image"*/
      $(thumb).attr('type', 'image');
      
      /* Set productivity panel in all images*/
      $('#productivity-uploader-previews').children().each(function( index, value ){            
          set_panel($(this), $(this).attr('image'), $(this).attr('type'));
      });
      
      $element.image.data('productivity').file = data.image.file;
    }

    data.panel.action.setmain.on_complete = function () {

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

  /* Sets Image edit Panel  */
  function set_panel($element, image, type){
    add_panel(
      $element,
            {
              panel: {
                position: { top: 2, left: 2 },
                loader: show_loader,
                action: {
                  remove: {
                    on_error: on_error,
                  },
                  rotate: {
                    on_error: on_error,
                  },
                  setmain: {
                    on_error: on_error,
                  }
                }
              },
              image: {
                file: image,
                type: type,
                width: null,
                height: null
              }
            }
        );
  }

  /* Get Image objects by url from Magento page*/
  function get_images_from_url(url){
    return $(':not(#productivity-panel)').find('img[src$="' + url + '"]');
  }

  /* Returns images sizes (width & height) */
  function get_images_sizes(url){
    var images = get_images_from_url(url);
    var sizes = [];
    $(images).each(function( index, image ){     
      sizes[index] = {width : $(image).width(), height : $(image).height()} ;   
    });
    return sizes;
  }

});  

}(window.productivity = window.productivity || {}, jQuery));
