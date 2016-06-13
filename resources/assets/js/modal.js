(function(){
  this.Modal = function(){
    var self = this;

    self.init = function(modal){
      self.$modal = $(modal);
      self.bindEvents();
      self.animationTime = 200;
      self.hideOffsetTop = '-10px';
      self.$modal.css({top: self.hideOffsetTop});
    };

    self.bindEvents = function(){
      $('.reveal-modal[data-modal=' + self.$modal.attr('id') + ']')
        .on('click', self.showModal);
      self.$modal.find('.close-modal').on('click', self.hideModal);
      self.$modal.find('.modal-bg').on('click', self.hideModal);
    };

    self.showModal = function(){
      self.$modal.css({'display': 'block'});
      self.$modal.animate({opacity: 1, top: '0'}, self.animationTime);
    };

    self.hideModal = function(){
      self.$modal.animate({opacity: 0, top: self.hideOffsetTop}, self.animationTime, function(){
        self.$modal.css({'display': 'none'});
      });
    };

  }
})()
