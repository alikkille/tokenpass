/* ========================================================================
 * Bootstrap: button.js v3.3.5
 * http://getbootstrap.com/javascript/#buttons
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
+function(t){"use strict";
// BUTTON PLUGIN DEFINITION
// ========================
function e(e){return this.each(function(){var n=t(this),a=n.data("bs.button"),o="object"==typeof e&&e;a||n.data("bs.button",a=new s(this,o)),"toggle"==e?a.toggle():e&&a.setState(e)})}
// BUTTON PUBLIC CLASS DEFINITION
// ==============================
var s=function(e,n){this.$element=t(e),this.options=t.extend({},s.DEFAULTS,n),this.isLoading=!1};s.VERSION="3.3.5",s.DEFAULTS={loadingText:"loading..."},s.prototype.setState=function(e){var s="disabled",n=this.$element,a=n.is("input")?"val":"html",o=n.data();e+="Text",null==o.resetText&&n.data("resetText",n[a]()),
// push to event loop to allow forms to submit
setTimeout(t.proxy(function(){n[a](null==o[e]?this.options[e]:o[e]),"loadingText"==e?(this.isLoading=!0,n.addClass(s).attr(s,s)):this.isLoading&&(this.isLoading=!1,n.removeClass(s).removeAttr(s))},this),0)},s.prototype.toggle=function(){var t=!0,e=this.$element.closest('[data-toggle="buttons"]');if(e.length){var s=this.$element.find("input");"radio"==s.prop("type")?(s.prop("checked")&&(t=!1),e.find(".active").removeClass("active"),this.$element.addClass("active")):"checkbox"==s.prop("type")&&(s.prop("checked")!==this.$element.hasClass("active")&&(t=!1),this.$element.toggleClass("active")),s.prop("checked",this.$element.hasClass("active")),t&&s.trigger("change")}else this.$element.attr("aria-pressed",!this.$element.hasClass("active")),this.$element.toggleClass("active")};var n=t.fn.button;t.fn.button=e,t.fn.button.Constructor=s,
// BUTTON NO CONFLICT
// ==================
t.fn.button.noConflict=function(){return t.fn.button=n,this},
// BUTTON DATA-API
// ===============
t(document).on("click.bs.button.data-api",'[data-toggle^="button"]',function(s){var n=t(s.target);n.hasClass("btn")||(n=n.closest(".btn")),e.call(n,"toggle"),t(s.target).is('input[type="radio"]')||t(s.target).is('input[type="checkbox"]')||s.preventDefault()}).on("focus.bs.button.data-api blur.bs.button.data-api",'[data-toggle^="button"]',function(e){t(e.target).closest(".btn").toggleClass("focus",/^focus(in)?$/.test(e.type))})}(jQuery);