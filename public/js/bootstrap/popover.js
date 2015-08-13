/* ========================================================================
 * Bootstrap: popover.js v3.3.5
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
+function(t){"use strict";
// POPOVER PLUGIN DEFINITION
// =========================
function o(o){return this.each(function(){var n=t(this),r=n.data("bs.popover"),i="object"==typeof o&&o;(r||!/destroy|hide/.test(o))&&(r||n.data("bs.popover",r=new e(this,i)),"string"==typeof o&&r[o]())})}
// POPOVER PUBLIC CLASS DEFINITION
// ===============================
var e=function(t,o){this.init("popover",t,o)};if(!t.fn.tooltip)throw new Error("Popover requires tooltip.js");e.VERSION="3.3.5",e.DEFAULTS=t.extend({},t.fn.tooltip.Constructor.DEFAULTS,{placement:"right",trigger:"click",content:"",template:'<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'}),
// NOTE: POPOVER EXTENDS tooltip.js
// ================================
e.prototype=t.extend({},t.fn.tooltip.Constructor.prototype),e.prototype.constructor=e,e.prototype.getDefaults=function(){return e.DEFAULTS},e.prototype.setContent=function(){var t=this.tip(),o=this.getTitle(),e=this.getContent();t.find(".popover-title")[this.options.html?"html":"text"](o),t.find(".popover-content").children().detach().end()[// we use append for html objects to maintain js events
this.options.html?"string"==typeof e?"html":"append":"text"](e),t.removeClass("fade top bottom left right in"),
// IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
// this manually by checking the contents.
t.find(".popover-title").html()||t.find(".popover-title").hide()},e.prototype.hasContent=function(){return this.getTitle()||this.getContent()},e.prototype.getContent=function(){var t=this.$element,o=this.options;return t.attr("data-content")||("function"==typeof o.content?o.content.call(t[0]):o.content)},e.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".arrow")};var n=t.fn.popover;t.fn.popover=o,t.fn.popover.Constructor=e,
// POPOVER NO CONFLICT
// ===================
t.fn.popover.noConflict=function(){return t.fn.popover=n,this}}(jQuery);