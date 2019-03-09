$(function(){


  $('.copy-container h1').addClass('move');

  $(window).scroll(function(){

    $(".copy-container").each(function(){

      var imgPos = $(this).offset().top;

      var scroll = $(window).scrollTop();

      var windowHeight = $(window).height();

      if (scroll > imgPos - windowHeight + windowHeight/5){

        $(this).find("h1").removeClass('move');

      } else {

        $(this).find("h1").addClass('move');

      }

    });

  });


  $('.copy-container h2').addClass('move');

  $(window).scroll(function(){

    $(".copy-container").each(function(){

      var imgPos = $(this).offset().top;

      var scroll = $(window).scrollTop();

      var windowHeight = $(window).height();

      if (scroll > imgPos - windowHeight + windowHeight/5){

        $(this).find("h2").removeClass('move');

      } else {

        $(this).find("h2").addClass('move');

      }

    });

  });


  $('.copy-container h1').addClass('move');

  $(window).scroll(function(){

    $(".copy-container").each(function(){

      var imgPos = $(this).offset().top;

      var scroll = $(window).scrollTop();

      var windowHeight = $(window).height();

      if (scroll > imgPos - windowHeight + windowHeight/5){

        $(this).find("h1").removeClass('move');

      } else {

        $(this).find("h1").addClass('move');

      }

    });

  });

  // 文字数カウント
  $('#js-count').keyup(function(){

    var count = $(this).val().length;

    $('#js-count-view').text(count);

  });


  // メッセージ表示
  var $jsShowMsg = $('#js-show-msg');
  var msg = $jsShowMsg.text();
  if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
    $jsShowMsg.slideToggle('slow');
    setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 3000);
    }

});
