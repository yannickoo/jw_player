<?php
/**
 * @file
 * Contains \Drupal\jw_player\Form\JwplayerSettingsForm.
 */

namespace Drupal\jw_player\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('jw_player.settings');
    $url = 'https://account.jwplayer.com/#/account';
    $form['account_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Cloud-Hosted Account Token'),
      '#description' => $this->t('Set the account token for a Cloud-Hosted Player, or leave empty if using a Self-Hosted Player. You can retrieve your token from <a href="@url">your account settings page at jwplayer.com</a>.', array(
          '@url' => $url,
        )),
      '#default_value' => $config->get('account_token', NULL),
    );

    $form['license_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Self-Hosted Player License Key'),
      '#description' => $this->t('If you have a premium account enter your key here. You can retrieve your license key from <a href="@url">your account settings page at jwplayer.com</a>.', array(
          '@url' => $url,
        )),
      '#default_value' => $config->get('license_key', NULL),
    );

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('jw_player.settings');
    $config->set('account_token', $form_state->getValue('account_token'));
    $config->set('license_key', $form_state->getValue('license_key'));
    $config->save();
  }
}
