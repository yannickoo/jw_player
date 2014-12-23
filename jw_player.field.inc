<?php

/**
 * @file
 * Implement an jw_player field, based on the file module's file field.
 */

use Drupal\jw_player\Entity\Jw_player;

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
  if ($key = $config->get('license_key')) {
    $variables['license_key'] = json_encode($key);
  }
  // Load defaults as the starting point.
  $default_settings = jw_player_default_settings();
  // Load preset if set.
  $preset_settings = $default_settings;
  if (!empty($variables['preset'])) {
    $preset = Jw_player::load($variables['preset']);
    // Additional check to ensure that the preset has actually loaded. This
    // prevents problems where a preset has been deleted but a field is still
    // configured to use it.
    if (!empty($preset)) {
      $preset_settings = $preset->settings;
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

  // Create a configuration array which will be passed to JWPlayer's JavaScript.
  $variables['config']['file'] = $variables['file_url'];

  // Resolve skin url.
  $skin = !empty($variables['skin']) ? jw_player_skins($variables['skin']) : '';
  $variables['skin'] = !empty($skin) ? file_create_url($skin->uri) : '';

  // Copy player variables into their own array to be set as JavaScript
  // configuration.
  // @todo Bad smell here. Refactoring needed.
  $player_variables = array('width', 'height', 'controlbar', 'playlist.position', 'playlist.size', 'skin', 'autoplay', 'base', 'advertising');
  foreach ($player_variables as $key) {
    if (!empty($variables[$key])) {
      $variables['config'][$key] = $variables[$key];
    }
  }

  $variables['config']['primary'] = $variables['mode'];

  // Unset advertising if is not set.
  if (empty($variables['config']['advertising']['client'])) {
    unset($variables['config']['advertising']);
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
  $jw_player_inline_js_code = json_encode($variables['config']);
  $variables['jw_player_inline_js_code'] = stripslashes($jw_player_inline_js_code);
}
