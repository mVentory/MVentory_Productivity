jQuery(document).ready(function ($) {
  var $form = $('#product_addtocart_form');
  var $menus = $form.find('.tm-image-editor-menu');
  var $updImage;
  var $updImageEditor;

  $menus
    .parent()
    .on('mouseenter', function () {
      var $this = $(this);

      if ($this.css('position') == 'absolute')
        var offset = { top: 0, left: 0 };
      else
        var offset = $this.offset();

      if(!$this.children('.tm-image-editor-menu').hasClass('disabled')) {
        $this
          .children('.tm-image-editor-menu')
          .css({
            top: offset.top + $this.height() - 10,
            left: offset.left + 10
          })
          .show();
      }
    })
    .on('mouseleave', function () {
      $(this)
        .children('.tm-image-editor-menu')
        .hide();
    });

  $menus
    .children('.rotate-image')
    .filter('.rotate-left')
      .on('click', { rotate: 'left' }, rotate_button_click_handler)
    .end()
    .filter('.rotate-right')
      .on('click', { rotate: 'right' }, rotate_button_click_handler);

  $menus
    .children('.remove-image')
    .on('click', remove_button_click_handler);

  $menus
    .children('.set-main-image')
    .on('click', set_main_button_click_handler);

  function rotate_image (params, complete) {
    var productId = $form.find('input[name="product"]').val();

    if ($updImage.parent().parent().is('li'))
      var thumb = true;
    else
      var thumb = false;

    $.ajax({
      url: _tm_image_editor_rotate_url,
      type: 'POST',
      dataType: 'json',
      data: {
        params: params,
        thumb: thumb,
        productId: productId
      },
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $updImage.css('border');
        $updImage.css('border','1px solid red');
        setTimeout(function(){$updImage.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + productId + '\nImage file: ' + file
              + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        $updImage.prop('src', data.image);
        $updImage.parent('a')
          .prop('href', '/media/catalog/product' + data.base);
        $updImageEditor.children('input').val(data.params);
      },
      complete: complete
    });
  }

  function remove_image (params, product_id, complete) {
    if($updImage.parent().parent().is('li')) {
      var thumb = true;
    } else {
      var thumb = false;
    }

    $.ajax({
      url: _tm_image_editor_remove_url,
      type: 'POST',
      dataType: 'json',
      data: { params: params, product: product_id, thumb: thumb },
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $updImage.css('border');
        $updImage.css('border','1px solid red');
        setTimeout(function(){$updImage.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + product_id + '\nImage file: ' + params.file + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        //console.log(data);
        if(data) {
          $updImage
            .prop('src', data.image);
        }
        if(thumb) {
          $updImage.remove();
        }
      },
      complete: complete
    });
  }

  function set_main_image (params, main_image_params, product_id, complete) {
    var $img = $form.find('.product-image img')

    var $thumb = $form
                   .find('img')
                   .filter('[src$="' + params.file + '"]')

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
        $thumb.prop('src', data.thumbImage);
        $img.prop('src', data.mainImage);

        $img
          .parents('.product-image')
          .nextAll('.tm-image-editor-menu')
          .children('input[name="_productivity_image_params"]')
          .val(data.main_image_params);

        $thumb
          .parents('li')
          .find('input[name="_productivity_image_params"]')
          .val(data.params);

        $thumb.css('opacity', '1');
        $img.css('opacity', '1');
      },
      complete: complete
    });
  }

  function rotate_button_click_handler (event) {
    var $this = $(this);
    var $editor = $this.parent();

    /*
    $this
      .parent()
      .children('.rotate-image')
      .off('click', rotate_button_click_handler);
    */

    $editor
      .addClass('disabled')
      .hide();

    event.preventDefault();

    var params = $editor
                   .children('input[name="_productivity_image_params"]')
                   .val();

    params = $.parseJSON(params);
    params.rotate = event.data.rotate;

    $updImageEditor = $editor;
    $updImage = $editor.parent().find('img');
    $updImage.css('opacity', '0.5');

    $updImage.parent().append('<div class="image-editor-loader"></div>');

    if (!$updImage.parent().parent().is('li'))
      $('.image-editor-loader').show();

    rotate_image(params, function () {
      $updImage.css('opacity', '1');
      $('.image-editor-loader').remove();
      $this.parent().removeClass('disabled');
    });

    return false;
  }

  function remove_button_click_handler (event) {
    var $this = $(this);
    var $editor = $this.parent();

    /*$this.off('click', remove_button_click_handler);*/

    event.preventDefault();

    var params = $editor
                   .children('input[name="_productivity_image_params"]')
                   .val();

    params = $.parseJSON(params);

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    $updImage = $this.parent().parent().find('img');

    remove_image(params, product_id, function () {
      $this
        .parent()
        .hide();
    });
  }


  function set_main_button_click_handler (event) {
    var $this = $(this);
    var $editor = $this.parent();

    /*$this.off('click', set_main_button_click_handler);*/
    $this
      .parent()
      .addClass('disabled')
      .hide();

    event.preventDefault();

    var params = $editor
                   .children('input[name="_productivity_image_params"]')
                   .val();

    params = $.parseJSON(params);

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    var $main_image_input = $form
                              .find('.product-image')
                              .parent()
                              .find('input[name="_productivity_image_params"]');

    var $mainImageEditor = $main_image_input.parent();
    $mainImageEditor.addClass('disabled').hide();

    var main_image_params = $main_image_input.val();
    main_image_params = $.parseJSON(main_image_params);

    var $mainImage = $form.find('.product-image img')
    var $thumbImage = $form
                        .find('img')
                        .filter('[src$="' + params.file + '"]')

    $mainImage.css('opacity', '0.5');
    $thumbImage.css('opacity', '0.5');

    $mainImage.parent().append('<div class="image-editor-loader"></div>');
    $('.image-editor-loader').show();

    set_main_image(params, main_image_params, product_id, function () {
      $('.image-editor-loader').remove();
      $this.parent().removeClass('disabled');
      $mainImageEditor.removeClass('disabled');
    });

    var link = $thumbImage.parent('a').prop('href');
    $thumbImage.parent('a').prop('href', $mainImage.parent('a').prop('href'));
    $mainImage.parent('a').prop('href', link);
  }
});
