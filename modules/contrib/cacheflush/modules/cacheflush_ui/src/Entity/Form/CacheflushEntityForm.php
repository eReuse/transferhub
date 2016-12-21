<?php

/**
 * @file
 * Contains Drupal\cacheflush_ui\Entity\Form\CacheflushEntityForm.
 */

namespace Drupal\cacheflush_ui\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\cacheflush\Controller\CacheflushApi;

/**
 * Form controller for Cacheflush entity edit forms.
 *
 * @ingroup cacheflush
 */
class CacheflushEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* @var $entity \Drupal\cacheflush\Entity\CacheflushEntity */
    $form = parent::buildForm($form, $form_state);
    $form['title'] = array(
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->getTitle(),
      '#required' => TRUE,
    );

    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $this->entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    );

    $this->presetForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    // Mark the entity as requiring validation.
    $entity->setValidationRequired(!$form_state->getTemporaryValue('entity_validated'));

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);
    // Call validation function for tabs.
    foreach ($form_state->getStorage()['cacheflush_tabs'] as $tab => $value) {
      $value['validation']($tab, $form, $form_state);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->setData($form_state->getStorage()['presets']);
    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Cacheflush entity.', [
              '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Cacheflush entity.', [
              '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.cacheflush.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function presetForm(&$form, &$form_state) {

    $storage = $form_state->getStorage();
    // Form element, vertical tab parent.
    $form['cacheflush_vertical_tabs'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 50,
    );

    // Add vertical tabs.
    $storage['cacheflush_tabs'] = \Drupal::moduleHandler()->invokeAll('cacheflush_ui_tabs');
    $original_tabs = cacheflush_ui_cacheflush_ui_tabs();
    foreach ($storage['cacheflush_tabs'] as $key => $value) {
      $form[$key] = array(
        '#type' => 'details',
        '#title' => \Drupal\Component\Utility\SafeMarkup::checkPlain($value['name']),
        '#group' => 'cacheflush_vertical_tabs',
        '#weight' => isset($value['weight']) ? $value['weight'] : NULL,
        '#attributes' => isset($original_tabs[$key]) ? array('class' => array('original_tabs')) : array(),
        '#tree' => TRUE,
      );
    }

    // Adding table elemnts to tabs.
    $storage['preset_options'] = CacheflushApi::create(\Drupal::getContainer())->getOptionList();
    $data = $this->entity->getData();
    foreach ($storage['preset_options'] as $key => $value) {
      // Special tab element added only if there module are instaled.
      if ($value['category'] == 'vertical_tabs_often' && !\Drupal::moduleHandler()->moduleExists($key)) {
        continue;
      }
      $form[$value['category']][$key] = array(
        '#type' => 'checkbox',
        '#title' => \Drupal\Component\Utility\SafeMarkup::checkPlain($key),
        '#default_value' => isset($data[$key]) ? 1 : 0,
        '#description' => \Drupal\Component\Utility\SafeMarkup::checkPlain($value['description']),
      );
    }

    $this->tabsDescription($form);
    $storage['presets'] = array();
    $storage['data'] = $data;
    $form_state->setStorage($storage);
  }

  /**
   * Update form tabs with Notes.
   */
  public function tabsDescription(&$form) {

    $form['cacheflush_form_mani_note'] = array(
      '#type' => 'item',
      '#title' => t('Cache sources'),
      '#weight' => 40,
      '#description' => t('Select below the different cache sources you wish to clear when your preset is executed. Don`t be afraid to select them, all these are flushed when you normally clear all the caches. Select only those you need for better performance.'),
    );

    $form['vertical_tabs_core']['note'] = array(
      '#type' => 'item',
      '#title' => t('Note'),
      '#description' => t('Select any of the cache database tables below, to be truncated when this preset is executed.'),
      '#weight' => -10,
    );

    $form['vertical_tabs_functions']['note'] = array(
      '#type' => 'item',
      '#title' => t('Note'),
      '#description' => t('Select any of the below functions to be run when this preset is executed.'),
      '#weight' => -10,
    );

    $form['vertical_tabs_custom']['note'] = array(
      '#type' => 'item',
      '#title' => t('Note'),
      '#description' => t('Select any of the tables defined by contributed modules to be flushed when this preset is executed.'),
      '#weight' => -10,
    );

    $form['vertical_tabs_often']['note'] = array(
      '#type' => 'item',
      '#title' => t('Note'),
      '#description' => t('Some contrib modules have unique ways to store their cache, or to flush them.<br />These require custom configuration, so if you can`t find some of your contrib modules here, please submit us an issue on <a href="@url">http://drupal.org/project/cacheflush/issues/</a><br />
Select any from the list below to clear when this preset is executed.', array('@url' => 'http://drupal.org/project/issues/cacheflush/')),
      '#weight' => -10,
    );
  }

}
