$(document).on('scroll', function (e) {
  var stop = Math.round($(window).scrollTop());
  if (stop > 1){
    $('.landing-nav').removeClass('pristine');
  } else {
    $('.landing-nav').addClass('pristine');
  }
});

$('.toggle-mobile-nav').on('click', function (e) {
  var $nav = $('#mobile-nav')
  if ($nav.hasClass('active')) {
    $nav.removeClass('active');
  } else {
    $nav.addClass('active');
  }
});

$('[data-tooltip]').on('click', function (e) {
  var $this = $(this);
  var uniqid = $this.data('tooltipid');
  if (uniqid) {
    $('#' + uniqid).remove();
    $this.data('tooltipid', null)
  } else {
    var text = $this.data('tooltip');
    uniqid = Date.now();
    $this.data('tooltipid', uniqid);
    var $tooltip = $.parseHTML('<div id="' + uniqid + '" class="tooltip">' + text + '</div>');
    $this.append($tooltip);
  }
});

$('.active-toggle-module').on('click', function(e) {
  var $this = $(this);
  var currentState = $this.attr('data-toggle') === 'true';
  $this.attr('data-toggle', !currentState);
});