<?php
/**
 * @file
 * Contains \Drupal\jw_player\Entity\Jw_player.
 */
namespace Drupal\jw_player\Entity;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\jw_player\Jw_playerInterface;
/**
 * Defines the JW Player preset entity.
 *
 * @ConfigEntityType(
 *   id = "jw_player",
 *   label = @Translation("JWP Player preset"),
 *   handlers = {
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
 *    "edit-form" = "/admin/config/media/jw_player/{jw_player}",
 *    "delete-form" = "/admin/config/media/jw_player/{jw_player}/delete"
 *   }
 * )
 */
class Jw_player extends ConfigEntityBase implements Jw_playerInterface {

  /**
   * The ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Label.
   *
   * @var string
   */
  public $label;

  /**
   * Description.
   *
   * @var string
   */
  public $description;

  public $settings = array();

}
