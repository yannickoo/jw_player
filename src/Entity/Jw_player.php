<?php
/**
 * @file
 * Contains \Drupal\jw_player\Entity\Jw_player.
 */
namespace Drupal\jw_player\Entity;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\jw_player\Jw_playerInterface;
/**
 * Defines the Example entity.
 *
 * @ConfigEntityType(
 *   id = "jw_player",
 *   label = @Translation("Jw player"),
 *   controllers = {
 *     "list_builder" = "Drupal\jw_player\Controller\Jw_playerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\jw_player\Form\JwplayerPresetAdd",
 *       "edit" = "Drupal\jw_player\Form\JwplayerPresetAdd",
 *       "delete" = "Drupal\jw_player\Form\JwplayerDeleteForm"
 *     }
 *   },
 *   config_prefix = "preset",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *    "edit-form" = "jw_player.edit",
 *    "delete-form" = "jw_player.delete"
 *   }
 * )
 */

class Jw_player extends ConfigEntityBase implements Jw_playerInterface {
  /**
   * The Example ID.
   *
   * @var string
   */
  public $id;
  /**
   * The Example UUID.
   *
   * @var string
   */
  public $uuid;
  /**
   * The Example label.
   *
   * @var string
   */
  public $label;
  // Your specific configuration property get/set methods go here,
  // implementing the interface.
}
