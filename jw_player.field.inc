<?php

/**
 * @file
 * Implement an jw_player field, based on the file module's file field.
 */

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Prepares variables for jw_player formatter templates.
 *
 * Default template: jw_player.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *
 */
function template_preprocess_jw_player(&$variables) {
  $config = \Drupal::config('jw_player.settings');
  // Load defaults as the starting point.
  $default_settings = jw_player_default_settings();
  // Load preset if set.
  $preset_settings = array();
  if (!empty($variables['preset'])) {
    $preset = jw_player_preset_load((array)$variables['preset']);
    // Additional check to ensure that the preset has actually loaded. This
    // prevents problems where a preset has been deleted but a field is still
    // configured to use it.
    if (!empty($preset)) {
      $preset_settings = $preset[$variables['preset']]->settings;
    }
  }

  // Get any preset override options that were sent through the formatter or
  // theme call.
  $options = array();
  if (isset($variables['options'])) {
    $options = $variables['options'];
    unset($variables['options']);
  }

  // Merge all variables together. Preset settings take priority over defaults,
  // variables passed directly to the theme function take priority over both.
  $variables = array_merge($default_settings, $preset_settings, $options, $variables);

  // Give each instance of the player a unique id. A random hash is used in
  // place of drupal_html_id() due to potentially conflicting ids in cases where
  // the entire output of the theme function is cached.
  $variables['html_id'] = md5(rand());

  // Create a configuration array which will be passed to JWPlayer's JavaScript.
  $variables['config']['file'] = $variables['file_url'];

  // Resolve skin url
  $skin = !empty($variables['skin']) ? jw_player_skins($variables['skin']) : '';
  $variables['skin'] = !empty($skin) ? file_create_url($skin->uri) : '';

  // Copy player variables into their own array to be set as JavaScript
  // configuration.
  // @todo Bad smell here. Refactoring needed.
  $player_variables = array('width', 'height', 'controlbar', 'playlist.position', 'playlist.size', 'skin', 'autoplay');
  foreach ($player_variables as $key) {
    if (!empty($variables[$key])) {
      $variables['config'][$key] = $variables[$key];
    }
  }
  // Initalize the player modes. The order of this array determines which
  // playback mode will be tried first before the browser falls back to the next
  // option. The default is html5 first, but this can be overridden by a preset
  // (see the code directly below).
  $variables['config']['modes'] = array(
    array('type' => 'html5'),
    array(
      'type' => 'flash',
      'src' => file_create_url(libraries_get_path('jwplayer') . '/player.swf'),
    ),
  );

  // If the preset has the primary mode set, modify the modes array so that it
  // comes first.
  if (isset($variables['mode'])) {
    foreach ($variables['config']['modes'] as $key => $value) {
      if ($value['type'] == $variables['mode']) {
        unset($variables['config']['modes'][$key]);
        array_unshift($variables['config']['modes'], $value);
      }
    }
  }

  // Copy over all enabled plugins into the 'config' section as this is the key
  // that is sent over to the player.
  if (!empty($variables['plugins'])) {
    foreach ($variables['plugins'] as $plugin => $info) {
      if (!$info['enable']) {
        continue;
      }
      $variables['config']['plugins'][$plugin] = $info;
    }
  }
  if ($config->get('jw_player_inline_js')) {
    $jw_player_inline_js_code = new JsonResponse($variables['config']);
    $variables['jw_player_inline_js_code'] = stripslashes($jw_player_inline_js_code->getContent());
  }
  // If inline is not selected, add all relevant JavaScript now.
  else {
    $setting = array(
      'type' => 'setting',
      'data' => array('jw_player' => array($variables['html_id'] => $variables['config'])),
    );
    $config->set('jw_player_setting_js', $setting);
    $config->save();
  }
}