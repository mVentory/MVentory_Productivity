jQuery(document).ready(function ($) {
  var $form = $('#product_addtocart_form');
  var $menus = $form.find('.tm-image-editor-menu');
  var $updImage;
  var $updImageEditor;

  $menus
    .parent()
    .on('mouseenter', function () {
      var $this = $(this);

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

  function rotate_image (file, rotate, complete) {

    var imageWidth = $updImage.width();
    var imageHeight = $updImage.height();

    var productId = $form.find('input[name="product"]').val();

    if($updImage.parent().parent().is('li')) {
      var thumb = true;
    } else {
      var thumb = false;
    }

    $.ajax({
      url: _tm_image_editor_rotate_url,
      type: 'POST',
      dataType: 'json',
      data: { file: file, rotate:  rotate,
              imageWidth: imageWidth, imageHeight: imageHeight,
              thumb: thumb, productId: productId},
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $updImage.css('border');
        $updImage.css('border','1px solid red');
        setTimeout(function(){$updImage.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + productId + '\nImage file: ' + file + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        $updImage.prop('src', data.image);
        $updImage.parent('a').prop('href', '/media/catalog/product' + data.base);
        $updImageEditor.children('input').val(data.base);
      },
      complete: complete
    });
  }

  function remove_image (file, product_id, complete) {
    if($updImage.parent().parent().is('li')) {
      var thumb = true;
    } else {
      var thumb = false;
    }
    var imageWidth = $updImage.width();
    var imageHeight = $updImage.height();

    $.ajax({
      url: _tm_image_editor_remove_url,
      type: 'POST',
      dataType: 'json',
      data: { file: file, product: product_id , thumb: thumb,
              imageWidth: imageWidth, imageHeight: imageHeight},
      error: function (jqXHR, status, errorThrown) {
        var currentBorder = $updImage.css('border');
        $updImage.css('border','1px solid red');
        setTimeout(function(){$updImage.css('border',currentBorder)}, 5000);
        alert('Product ID: ' + productId + '\nImage file: ' + file + '\nStatus: ' + status);
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

  function set_main_image (thumbImage, mainImage, product_id, complete) {
    var $img = $form.find('.product-image img')

    var $thumb = $form
                   .find('img')
                   .filter('[src$="' + thumbImage + '"]')

    var imageThumbWidth = $thumb.width();
    var imageThumbHeight = $thumb.height();

    var imageWidth = $img.width();
    var imageHeight = $img.height();

    $.ajax({
      url: _tm_image_editor_setmain_url,
      type: 'POST',
      dataType: 'json',
      data: { product: product_id, thumbImage: thumbImage, mainImage: mainImage,
              imageThumbWidth: imageThumbWidth, imageThumbHeight: imageThumbHeight,
              imageWidth: imageWidth, imageHeight: imageHeight  },
      error: function (jqXHR, status, errorThrown) {
        alert('Product ID: ' + productId + '\nImage file: ' + file + '\nStatus: ' + status);
      },
      success: function (data, status, jqXHR) {
        //console.log(data);
        $thumb.prop('src', data.thumbImage);
        $img.prop('src', data.mainImage);
        $thumb.css('opacity', '1');
        $img.css('opacity', '1');
      },
      complete: complete
    });
  }

  function rotate_button_click_handler (event) {
    var $this = $(this);
    /*
    $this
      .parent()
      .children('.rotate-image')
      .off('click', rotate_button_click_handler);
    */
    $this
      .parent()
      .addClass('disabled')
      .hide();
    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();
    $updImageEditor =  $this.parent();
    $updImage = $this.parent().parent().find('img');
    $updImage.css('opacity', '0.5');

    $updImage.parent().append('<div class="image-editor-loader"></div>');
    if(!$updImage.parent().parent().is('li')) {
      $('.image-editor-loader').show();
    }

    var rotate = event.data.rotate;

    rotate_image(image, rotate, function () {
      //$this.on('click', { rotate: rotate }, rotate_button_click_handler);
      $updImage.css('opacity', '1');
      $('.image-editor-loader').remove();
      $this.parent().removeClass('disabled');
    });

    return false;
  }

  function remove_button_click_handler (event) {
    var $this = $(this);

    /*$this.off('click', remove_button_click_handler);*/

    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    $updImage = $this.parent().parent().find('img');

    remove_image(image, product_id, function () {
      $this
        .parent()
        .hide();
    });
  }


  function set_main_button_click_handler (event) {
    var $this = $(this);

    /*$this.off('click', set_main_button_click_handler);*/
    $this
      .parent()
      .addClass('disabled')
      .hide();

    event.preventDefault();

    var image = $this
                  .parent()
                  .children('input')
                  .val();

    var product_id = $form
                       .find('input[name="product"]')
                       .val();

    var $main_image_input = $form
                              .find('.product-image')
                              .parent()
                              .find('input[name="_tm_image_file"]');

    var $mainImageEditor = $main_image_input.parent();
    $mainImageEditor.addClass('disabled').hide();

    var mainImage = $main_image_input.val();

    var $mainImage = $form.find('.product-image img')
    var $thumbImage = $form
                        .find('img')
                        .filter('[src$="' + image + '"]')

    $mainImage.css('opacity', '0.5');
    $thumbImage.css('opacity', '0.5');

    $mainImage.parent().append('<div class="image-editor-loader"></div>');
    $('.image-editor-loader').show();

    set_main_image(image, mainImage, product_id, function () {
      $('.image-editor-loader').remove();
      $this.parent().removeClass('disabled');
      $mainImageEditor.removeClass('disabled');
    });

    $thumbImage.parent('a').prop('href', '/media/catalog/product' + $main_image_input.val());
    $mainImage.parent('a').prop('href', '/media/catalog/product' + image);

    $this.parent().children('input').val($main_image_input.val());
    $main_image_input.val(image);
  }
});
