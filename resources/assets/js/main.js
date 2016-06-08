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
  console.log('clicked')
  var $this = $(this);
  var currentState = parseInt($this.attr('data-toggle'));
  console.log(currentState);
  var newState = currentState ? 0 : 1;
  console.log(newState);
  $this.attr('data-toggle', newState);
});