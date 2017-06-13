<?php

namespace Drupal\Tests\hal\Functional\EntityResource\Item;

use Drupal\Tests\rest\Functional\AnonResourceTestTrait;

/**
 * @group hal
 */
class ItemHalJsonAnonTest extends ItemHalJsonTestBase {

  use AnonResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['hal'];

  /**
   * {@inheritdoc}
   */
  protected static $format = 'hal_json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/hal+json';

}
