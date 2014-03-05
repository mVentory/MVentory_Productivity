jQuery(document).ready(function ($) {
  if (typeof productivity === 'undefined')
    return;

  var $form = $('#product_addtocart_form');
  var $panel = $('#productivity-image-edit-panel');
  var $menus = $form.find('.tm-image-editor-menu');
  var $updImage;
  var $updImageEditor;

  for (var type in productivity.selector) {
    if (!productivity.selector.hasOwnProperty(type))
      continue;

    var selector = productivity.wrapper[type]
                    ? productivity.wrapper[type]
                      : productivity.selector[type];

    $(selector).on(
      {
        mouseenter: image_mouseenter_handler,
        mouseleave: panel_mouseleave_handler
      },
      {
        type: type
      }
    );
  }

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

  function rotate_image ($img, params, type, complete) {
    var productId = $form.find('input[name="product"]').val();

    $.ajax({
      url: _tm_image_editor_rotate_url,
      type: 'POST',
      dataType: 'json',
      data: {
        params: params,
        thumb: type == 'thumbnail',
        productId: productId
      },
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $img.css('border');
        $img.css('border','1px solid red');
        setTimeout(function(){$img.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + productId + '\nImage file: ' + file
              + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        if (!data.success) {
          var currentBorder = $img.css('border');
          $img.css('border','1px solid red');
          setTimeout(function(){$img.css('border',currentBorder)}, 5000);
          alert('Product ID: ' + productId + '\nImage file: ' + file
                + '\nStatus: ' + status);

          return;
        }

        data = data.data;

        $img.prop('src', data.image);
        $img.parent('a')
          .prop('href', '/media/catalog/product' + data.base);
      },
      complete: complete
    });
  }

  function remove_image ($img, params, product_id, type, complete) {
    var thumb = type == 'thumbnail';

    $.ajax({
      url: _tm_image_editor_remove_url,
      type: 'POST',
      dataType: 'json',
      data: { params: params, product: product_id, thumb: thumb },
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $img.css('border');
        $img.css('border','1px solid red');
        setTimeout(function(){$img.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + product_id + '\nImage file: ' + params.file + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        //console.log(data);
        if (!data.success) {
          var currentBorder = $img.css('border');
          $img.css('border','1px solid red');
          setTimeout(function(){$img.css('border',currentBorder)}, 5000);
          alert('Product ID: ' + product_id + '\nImage file: ' + params.file + '\nStatus: ' + status);

          return;
        }

        data = data.data;

        if (data.image) {
          $img
            .prop('src', data.image);
        }
        if(thumb) {
          $img.remove();
        }
      },
      complete: complete
    });
  }

  function set_main_image ($image,
                           $thumb,
                           params,
                           main_image_params,
                           product_id,
                           complete) {

    $.ajax({
      url: _tm_image_editor_setmain_url,
      type: 'POST',
      dataType: 'json',
      data: { product: product_id,
              params: params,
              main_image_params: main_image_params },
      error: function (jqXHR, status, errorThrown) {
        alert('Product ID: ' + product_id + '\nImage file: ' + params.file + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        //console.log(data);

        if (!data.success) {
          alert('Product ID: ' + product_id + '\nImage file: ' + params.file + '\nStatus: ' + status);

          return;
        }

        data = data.data;

        $thumb.prop('src', data.thumbImage);
        $image.prop('src', data.mainImage);

        $thumb.css('opacity', '1');
        $image.css('opacity', '1');
      },
      complete: complete
    });
  }

  function image_mouseenter_handler (event) {
    var $this = $(this);
    var offset = $this.offset();

    if($panel.hasClass('disabled'))
      return;

    if (event.data.type == 'image')
      $panel.addClass('productivity-state-main-image');
    else
      $panel.removeClass('productivity-state-main-image');

    $panel
      .css({
        top: offset.top + $this.height() - 10,
        left: offset.left + 10
      })
      .show()
      .data({
        img: $this,
        type: event.data.type
      });
  }

  function panel_mouseleave_handler () {
    $panel.hide()
  }

  function rotate_button_click_handler (event) {
    event.preventDefault();

    var $img = $panel.data('img');
    var type = $panel.data('type');

    if (!($img && type))
      return false;

    if (productivity.wrapper[type]
        && !(($img = $img.find(productivity.selector[type]))
             && $img.length))
      return false;

    $img.css('opacity', '0.5');

    if (type == 'image')
      var $loader = $('<div class="image-editor-loader" />')
                      .appendTo($img.parent())
                      .show();

    $panel
      .addClass('disabled')
      .hide();

    var params = {
      file: get_filename_from_url($img.prop('src')),
      width: productivity.size[type].width,
      height: productivity.size[type].height,
      rotate: event.data.rotate
    };

    rotate_image($img, params, type, function () {
      $img.css('opacity', '1')

      if ($loader)
        $loader.remove();

      $panel.removeClass('disabled');
    });

    return false;
  }

  function remove_button_click_handler (event) {
    event.preventDefault();

    var $img = $panel.data('img');
    var type = $panel.data('type');

    if (!($img && type))
      return false;

    if (productivity.wrapper[type]
        && !(($img = $img.find(productivity.selector[type]))
             && $img.length))
      return false;

    var params = {
      file: get_filename_from_url($img.prop('src')),
      width: productivity.size[type].width,
      height: productivity.size[type].height
    };

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    remove_image($img, params, product_id, type, function () {
      $panel
        .hide();
    });

    return false;
  }


  function set_main_button_click_handler (event) {
    event.preventDefault();

    var $img = $panel.data('img');
    var type = $panel.data('type');

    if (!($img && type == 'thumbnail'))
      return false;

    if (productivity.wrapper[type]
        && !(($img = $img.find(productivity.selector[type]))
             && $img.length))
      return false;

    $panel
      .addClass('disabled')
      .hide();

    var $this = $(this);
    var $editor = $this.parent();

    /*$this.off('click', set_main_button_click_handler);*/

    var params = {
      file: get_filename_from_url($img.prop('src')),
      width: productivity.size['thumbnail'].width,
      height: productivity.size['thumbnail'].height
    };

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    var $main_image_input = $form
                              .find('.product-image')
                              .parent()
                              .find('input[name="_productivity_image_params"]');

    var $mainImage = productivity.wrapper.image
                       ? $(productivity.wrapper.image)
                           .find(productivity.selector.image)
                         : $(productivity.selector.image);

    var main_image_params = {
      file: get_filename_from_url($mainImage.prop('src')),
      width: productivity.size['image'].width,
      height: productivity.size['image'].height
    };

    $mainImage.css('opacity', '0.5');
    $img.css('opacity', '0.5');

    $mainImage.parent().append('<div class="image-editor-loader"></div>');
    $('.image-editor-loader').show();

    set_main_image(
      $mainImage,
      $img,
      params,
      main_image_params,
      product_id,
      function () {
        $('.image-editor-loader').remove();
        $panel.removeClass('disabled');
      }
    );

    var link = $img.parent('a').prop('href');
    $img.parent('a').prop('href', $mainImage.parent('a').prop('href'));
    $mainImage.parent('a').prop('href', link);
  }

  function get_filename_from_url (url) {
    return url.substring(
      url.lastIndexOf('/', url.lastIndexOf('/', url.lastIndexOf('/') - 1) - 1)
    );
  }
});
