<?php
/**
 * @file
 * Contains \Drupal\jw_player\Access\JwplayerSettingAccess.
 */
namespace Drupal\jw_player\Access;

use Drupal\Core\Access\AccessCheckInterface;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;;
/**
 * Checks access for displaying configuration translation page.
 */
class JwplayerSettingAccess implements  AccessCheckInterface  {
  /**
   * A user account to check access for.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;
  public function applies(Route $route){
    return ' _jw_player_setting_access';
  }
  /**
   * Constructs a CustomAccessCheck object.
   *
   * @param \Drupal\Core\Session\AccountInterface
   *   The user account to check access for.
   */
//  public function __construct(AccountInterface  $user) {
//    $this->user = $user;
//  }
  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {
    return $account->hasPermission('access all jwplayer') ? static::ALLOW : static::DENY;
  }
}
