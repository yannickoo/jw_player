// $Id: jw_player.js, v 0.1, 2009/07/28 12:11:24, skilip Exp $;
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
