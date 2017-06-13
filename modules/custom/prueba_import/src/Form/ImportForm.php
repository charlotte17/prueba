<?php

namespace Drupal\prueba_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\prueba_import\PruebaMigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationPluginManager;

/**
 * Class ImportForm.
 *
 * @package Drupal\prueba_import\Form
 */
class ImportForm extends FormBase {

  /**
   * Drupal\migrate\Plugin\MigrationPluginManager definition.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $pluginManagerMigration;
  /**
   * Constructs a new ImportForm object.
   */
  public function __construct(
    MigrationPluginManager $plugin_manager_migration
  ) {
    $this->pluginManagerMigration = $plugin_manager_migration;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.migration')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['type_of_csv'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of CSV'),
      '#options' => ['reward' => $this->t('Rewards'), 'program' => $this->t('Programs')],
      '#size' => 1,
    ];
    $form['upload_csv'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV'),
    ];
    $form['update_existing_records'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update existing records'),
      '#default_value' => 1,
    ];
    $form['import'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  
    $validators = ['file_validate_extensions' => ['csv']];
    $file = file_save_upload('upload_csv', $validators, FALSE, 0);
    if (isset($file)) {
      // File upload was attempted.
      if ($file) {
        $form_state->setValue('csv_path', $file->getFileUri());
      }
      else {
        // File upload failed.
        $form_state->setErrorByName('upload_csv', $this->t('The CSV file could not be uploaded.'));
      }
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $migration = 'reward_csv';
    switch ($form_state->getValue('type_of_csv')) {
      case 'reward':
        $migration = 'reward_csv';
        break;
    
    case 'program':
      $migration = 'program_csv';
      break;
    }
  
    /** @var Migration $migrationInstance */
    $definition = $this->pluginManagerMigration->getDefinition($migration);
    // Alter source file path from validator above.
    $definition['source']['path'] = $form_state->getValue('csv_path') ;
    $migrationInstance = $this->pluginManagerMigration->createStubMigration($definition);
    
    // Reset status.
    $status = $migrationInstance->getStatus();
    if ($status != MigrationInterface::STATUS_IDLE) {
      $migrationInstance->setStatus(MigrationInterface::STATUS_IDLE);
      drupal_set_message($this->t('Migration @id reset to Idle', ['@id' => $migration]), 'warning');
    }
    
    // Force updates or not.
    if ($form_state->getValue('update_existing_records')) {
      $migrationInstance->getIdMap()->prepareUpdate();
    }
    
    $executable = new MigrateExecutable($migrationInstance, new PruebaMigrateMessage());
    $result = $executable->import();
    
    if ($result ==  MigrationInterface::RESULT_COMPLETED) {
      drupal_set_message($this->t('Import was successful'));
    }
    else {
      drupal_set_message($this->t('The import was not successful, please review logs.'));
    }
  }

}
