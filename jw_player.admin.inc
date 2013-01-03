<?php

/**
 * @file
 *   Provides admin settings form(s)
 */

/**
 * General settings form
 */
function jw_player_settings_form($form) {

  drupal_set_title(t('JW Player general settings'));

  $form['jw_player_inline_js'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use inline javascript'),
    '#description' => t('With this option enabled JW Player configuration will be printed inline directly after the player markup. This can be useful if the player markup is cached as otherwise JW Player will not be loaded. The downside is that the player itself will be loaded on all pages.'),
    '#default_value' => variable_get('jw_player_inline_js', TRUE),
  );

  $form['jw_player_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Licence key'),
    '#description' => t('If you have a premium account enter your key here'),
    '#default_value' => variable_get('jw_player_key', NULL),
  );
  return system_settings_form($form);
}