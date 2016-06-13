(function(){
  this.Modal = function(){
    var self = this;

    self.init = function(modal){
      self.$modal = $(modal);
      self.bindEvents();
    };

    self.bindEvents = function(){
      $('.reveal-modal[data-modal=' + self.$modal.attr('id') + ']')
        .on('click', self.showModal);
      self.$modal.find('.close-modal').on('click', self.hideModal);
      self.$modal.find('.modal-bg').on('click', self.hideModal);
    };

    self.showModal = function(){
      self.$modal.show();
    };

    self.hideModal = function(){
      self.$modal.hide();
    };

  }
})()
