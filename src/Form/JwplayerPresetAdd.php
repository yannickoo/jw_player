<?php
/**
 * @file
 * Contains \Drupal\jw_player\Form\JwplayerSettingsForm.
 */

namespace Drupal\jw_player\Form;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
/**
 * Configure search settings for this site.
 */
class JwplayerPresetAdd extends EntityForm {

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\page_manager\PageInterface
   */
  protected $entity;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Construct a new PageFormBase.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory
   *   The entity query factory.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
//    $preset = $form_state['item'];
//    $settings = $preset->settings;
    $form = parent::form($form, $form_state);
    $preset = $this->entity;
    // This is a hack. CTools adds a hierarchy for export_key in form of
    // $form['info][$export_key] (see line 1007 of
    // ctools/plugins/export_ui/ctools_export_ui.class.php).
    // The FAPI machine name object doesn't seem to work well with fields present
    // in different levels of hierarchy, hence we move the fields over to the same
    // level in the hierarchy.
    $form['label'] = array(
      '#type' => 'textfield',
      '#size' => 20,
      '#maxlength' => 255,
      '#title' => $this->t('Preset name'),
      '#description' => $this->t('Enter name for the preset.'),
      '#default_value' =>  $preset->label(),
      '#required' => TRUE,
      '#weight' => 0,
    );

    $form['id'] = array(
      '#title' => t('Machine name'),
      '#type' => 'machine_name',
      '#default_value' => $preset->id(),
      '#machine_name' => array(
        'exists' =>  array($this, 'exists'),
      ),
      '#weight' => 1,
      '#description' => t('Enter the machine name for the preset. It must be unique and contain only alphanumeric characters and underscores.'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#size' => 10,
      '#title' => t('Description'),
      '#description' => t('Summary for the preset.'),
      '#default_value' => $preset->description,
      '#weight' => 2,
    );

    $form['settings'] = array(
      '#type' => 'fieldset',
      '#title' => 'Settings',
      '#tree' => TRUE,
      '#weight' => 5,
    );

    $form['settings']['mode'] = array(
      '#type' => 'radios',
      '#title' => t('Embed mode'),
      '#description' => t('Select your primary embed mode. Choosing HTML5 primary means that modern browsers that also support flash will use the HTML5 player first where possible. While this is desirable, the Flash based player supports more features and is generally more reliable.'),
      '#options' => array(
        'flash' => t('Flash primary, HTML5 failover'),
        'html5' => t('HTML5 primary, Flash failover'),
      ),
      '#default_value' => isset($preset->settings['mode']) ? $preset->settings['mode'] : 'html5',
    );

    $form['settings']['width'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#title' => t('Width'),
      '#description' => t('Enter the width for this player.'),
      '#field_suffix' => ' ' . t('pixels'),
      '#default_value' => isset($preset->settings['width']) ? $preset->settings['width'] : NULL,
      '#required' => TRUE,
      '#weight' => 5,
    );

    $form['settings']['height'] = array(
      '#type' => 'textfield',
      '#size' => 10,
      '#title' => t('Height'),
      '#description' => t('Enter the height for this player.'),
      '#field_suffix' => ' ' . t('pixels'),
      '#default_value' => isset($preset->settings['height']) ? $preset->settings['height'] : NULL,
      '#required' => TRUE,
      '#weight' => 6,
    );

    $form['settings']['controlbar'] = array(
      '#title' => t('Controlbar Position'),
      '#type' => 'select',
      '#description' => t('Where the controlbar should be positioned.'),
      '#default_value' => !empty($preset->settings['controlbar']) ? $preset->settings['controlbar'] : 'none',
      '#options' => array(
        'none' => t('None'),
        'bottom' => t('Bottom'),
        'top' => t('Top'),
        'over' => t('Over'),
      ),
      '#weight' => 7,
    );

    // Skins.
    $skin_options = array();
    foreach (jw_player_skins() as $skin) {
      $skin_options[$skin->name] = drupal_ucfirst($skin->name);
    }

    $form['settings']['skin'] = array(
      '#title' => t('Skin'),
      '#type' => 'select',
      '#default_value' => !empty($preset->settings['skin']) ? $preset->settings['skin'] : FALSE,
      '#empty_option' => t('None (default skin)'),
      '#options' => $skin_options,
    );

    // Add preset plugin settings.
    foreach (jw_player_preset_plugins() as $plugin => $info) {
      $form['settings']['plugins']['#weight'] = 8;

      // Fieldset per plugin.
      $form['settings']['plugins'][$plugin] = array(
        '#type' => 'fieldset',
        '#title' => String::checkPlain($info['name']),
        '#description' => String::checkPlain($info['description']),
        '#tree' => TRUE,
        '#weight' => 10,
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
      );

      // Enable/disable plugin setting.
      $form['settings']['plugins'][$plugin]['enable'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable'),
        '#description' => String::checkPlain($info['description']),
//        '#default_value' => isset($settings['plugins'][$plugin]['enable']) ? $settings['plugins'][$plugin]['enable'] : FALSE,
      );

      // Add each config option specified in the plugin. Config options should be
      // in FAPI structure.
      if (is_array($info['config options']) and !empty($info['config options'])) {
        foreach ($info['config options'] as $option => $element) {
          // Note: Each config option must be a complete FAPI element, except for
          // the #title which is optional. If the #title is not provided, we use
          // the name of the config option as the title.
          if (!isset($element['#title'])) {
            $element['#title'] = drupal_ucfirst($option);
          }
          // Alter the default value if a setting has been saved previously.
          $element['#default_value'] = !empty($preset['plugins'][$plugin][$option]) ? $preset['plugins'][$plugin][$option] : $element['#default_value'];
          // Make the whole element visible only if the plugin is checked (enabled).
          $element['#states'] = array(
            'visible' => array(
              'input[name="settings[plugins][' . $plugin . '][enable]"]' => array('checked' => TRUE),
            ),
          );
          // Add the element to the FAPI structure.
          $form['settings']['plugins'][$plugin][$option] = $element;
        }
      }
    }

    $form['settings']['autoplay'] = array(
      '#title' => t('Autoplay'),
      '#type' => 'checkbox',
      '#description' => t('Set the video to autoplay on page load'),
      '#default_value' => !empty($preset->settings['autoplay']) ? $preset->settings['autoplay']: 'false',
      '#weight' => 4,
    );

    return $form;
  }
  public function exists($machine_name) {
    return (bool) $this->entityQuery->get('jw_player')
      ->condition('id', $machine_name)
      ->execute();
  }
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $preset = $this->entity;
    $status = $preset->save();
    if ($status) {
      drupal_set_message($this->t('Saved the %label Preset.', array(
        '%label' => $preset->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label Preset was not saved.', array(
        '%label' => $preset->label(),
      )));
    }
    $form_state->setRedirect('jw_player.list');
  }

}
