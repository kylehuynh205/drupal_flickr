<?php

namespace Drupal\flickr\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the flickr module.
 */
class DownloadControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "flickr DownloadController's controller functionality",
      'description' => 'Test Unit for module flickr and controller DownloadController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests flickr functionality.
   */
  public function testDownloadController() {
    // Check that the basic functions of module flickr.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
