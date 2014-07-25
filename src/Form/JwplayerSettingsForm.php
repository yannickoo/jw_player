<?php
/**
 * @file
 * Contains \Drupal\jw_player\Form\JwplayerSettingsForm.
 */

namespace Drupal\jw_player\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure search settings for this site.
 */
class JwplayerSettingsForm extends ConfigFormBase {


  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jwplayer_main_settings';
  }

  /**
   * Gets the roles to display in this form.
   *
   * @return \Drupal\user\RoleInterface[]
   *   An array of role objects.
   */
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $page = \Drupal::service('element_info')->getInfo('page');
    $page['#title'] = $this->t('JW Player general settings');

    $form['jw_player_inline_js'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use inline javascript'),
      '#description' => t('With this option enabled JW Player configuration will be printed inline directly after the player markup. This can be useful if the player markup is cached as otherwise JW Player will not be loaded. The downside is that the player itself will be loaded on all pages.'),
      '#default_value' => (bool) \Drupal::config('jw_player.settings')->get('jw_player_inline_js'),
    );

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = \Drupal::config('jw_player.settings');
    parent::submitForm($form, $form_state);
    $config->set('jw_player_inline_js', $form_state['values']['jw_player_inline_js']);
    $config->save();
  }
}