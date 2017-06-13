<?php

namespace Drupal\prueba_import;

use Drupal\migrate\MigrateMessageInterface;

class PruebaMigrateMessage implements MigrateMessageInterface {

  /**
   * Output a message from the migration.
   *
   * @param string $message
   *   The message to display.
   * @param string $type
   *   The type of message to display.
   *
   * @see drupal_set_message()
   */
  public function display($message, $type = 'status') {
    drupal_set_message($message, $type);
  }

}
