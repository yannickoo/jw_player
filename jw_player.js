(function ($) {

  /**
   * Attaches a JW Player element by using the JW Player Javascript Library
   */
  Drupal.behaviors.JWPlayer = {
    attach: function(context) {
      var players = Drupal.settings.jw_player;
      $.each(players, function(player_id, config) {
        if ($('#' + player_id, context).length) {

          if (config.events) {
            $.each(config.events, function(event, callback) {
              config.events[event] = eval('('+callback+')');
            });
          };

          jwplayer(player_id).setup(config);
        }
      });
    }
  };

})(jQuery);
