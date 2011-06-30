(function ($) {

  /**
   * 
   */
  Drupal.behaviors.JWPlayerFancybox = {
    attach: function(context) {
      $('a.jw-player-fancybox', context).once('jw_player-fancybox', function() {
    		$(this).fancybox({
    			'padding': 0,
    			'href': this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
    			'type': 'swf',
    			'swf': {'wmode': 'transparent', 'allowfullscreen': 'true'}
        });
      });
    }
  };

})(jQuery);
