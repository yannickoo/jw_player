(function ($) {

  /**
   * Attaches a JW Player element by using the JW Player Javascript Library
   */
  Drupal.behaviors.JWPlayer = {
    attach: function(context, settings) {
      $.each(settings.jw_player, function(player_id, config) {
        jwplayer(player_id).setup(config);

        if (config.events) {
          $.each(config.events, function(event, callback) {
            jwplayer(player_id)[event](eval(callback));
          });
        };
      });
    }
  };

})(jQuery);