<?php
/**
 * @file
 * Contains \Drupal\jw_player\JwPlayerConfigurationTest.
 */

namespace Drupal\jw_player\Tests;

use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the configuration of a jw player preset and creation of jw player
 * content.
 *
 * @group jw_player
 */
class JwPlayerConfigurationTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'jw_player',
    'libraries',
    'file',
    'field',
    'field_ui',
    'block'
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create jw_player content type.
    $this->drupalCreateContentType(array('type' => 'jw_player', 'name' => 'JW content'));
    // Place the breadcrumb, tested in fieldUIAddNewField().
    $this->drupalPlaceBlock('system_breadcrumb_block');
  }

  /**
   * Tests the jw player creation.
   */
  public function testJwPlayerCreation() {

    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer JW Player presets',
      'administer nodes',
      'create jw_player content',
      'administer content types',
      'administer node fields',
      'administer node display',
      'access administration pages'
    ));
    $this->drupalLogin($admin_user);

    // Add a random "Cloud-Hosted Account Token".
    $this->drupalPostForm('admin/config/media/jw_player/settings', array('account_token' => $token = $this->randomMachineName(8)), t('Save configuration'));
    // Create a jw player preset.
    $edit = array(
      'label' => 'Test preset',
      'id' => 'test_preset',
      'description' => 'Test preset description',
      'settings[mode]' => 'html5',
      'settings[width]' => 100,
      'settings[height]' => 100,
      'settings[advertising][client]' => 'vast',
      'settings[advertising][tag]' => 'www.example.com/vast',
      'settings[controlbar]' => 'bottom'
    );
    $this->drupalPostForm('admin/config/media/jw_player/add', $edit, t('Save'));
    $this->assertText('Saved the Test preset Preset.');
    // Make sure preset has correct values.
    $this->drupalGet('admin/config/media/jw_player/test_preset');
    $this->assertFieldByName('label', 'Test preset');
    $this->assertFieldByName('description', 'Test preset description');
    $this->assertFieldByName('settings[mode]', 'html5');
    $this->assertFieldByName('settings[advertising][client]', 'vast');
    $this->assertFieldByName('settings[advertising][tag]', 'www.example.com/vast');
    $this->assertFieldByName('settings[controlbar]', 'bottom');

    // Create a JW player format file field in JW content type.
    static::fieldUIAddNewField('admin/structure/types/manage/jw_player', 'video', 'Video', 'file', array(), array('settings[file_extensions]' => 'mp4'));
    $this->drupalPostForm('admin/structure/types/manage/jw_player/display', array('fields[field_video][type]' => 'jwplayer_formatter'), t('Save'));
    $this->drupalPostAjaxForm(NULL, NULL, 'field_video_settings_edit');
    $this->drupalPostForm(NULL, NULL, t('Update'));
    $this->drupalPostForm(NULL, NULL, t('Save'));
    // Make sure JW preset is correct.
    $this->assertText('Preset: test_preset');
    $this->assertText('width: 100');
    $this->assertText('height: 100');

    // Create a 'video' file, which has .mp4 extension.
    $text = 'Trust me I\'m a video';
    file_put_contents('temporary://myVideo.mp4', $text);
    // Create video content from JW content type.
    $edit = array(
      'title[0][value]' => 'Test video',
      'files[field_video_0]' => drupal_realpath('temporary://myVideo.mp4')
    );
    $this->drupalPostForm('node/add/jw_player', $edit, t('Save and publish'));
    $this->assertText('JW content Test video has been created.');

    $value = $this->xpath('//video/@id');
    $id = (string) $value[0]['id'];
    // Check the jw_player js.
    $this->assertRaw('jwplayer(\'' . $id . '\').setup( {"file":"' . file_create_url('public://myVideo.mp4') . '","width":100,"height":100,"controlbar":"bottom","advertising":{"client":"vast","tag":"www.example.com/vast"},"primary":"html5"})');
    // Make sure the hash is there.
    $this->assertTrue(preg_match('/jwplayer-[a-zA-Z0-9]{1,}$/', $id));
    // Check the library created because of cloud hosting.
    $this->assertRaw('<script src="//jwpsrv.com/library/' . $token . '.js"></script>');
  }

}
