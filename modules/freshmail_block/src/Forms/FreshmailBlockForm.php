<?php

namespace Drupal\freshmail_block\Forms;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\freshmail\Controller\FreshmailController;


/**
 * Freshmail configuration form
 */
class FreshmailBlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'freshmail_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['alert'] = array(
      array(
        '#type' => 'markup',
        '#markup' => '<div id="freshmail-alert"></div>',
      ),
    );
    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => \Drupal::currentUser()
        ->getEmail() ? \Drupal::currentUser()->getEmail() : '',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
      if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
        $form_state->setErrorByName('email', $this->t('Invalid e-mail'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $request = new FreshmailController();
    $freshmail_response = $request->addSubscriber($form_state->getValue('email'));

    if ($freshmail_response['status'] == 'OK') {
      drupal_set_message(t('E-mail added to list'));
      return;
    }

    if (isset($freshmail_response['errors'][0]['message'])) {
      drupal_set_message(t($freshmail_response['errors'][0]['message']), 'error');
    }
    else {
      drupal_set_message(t('Unknown error'), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'freshmail.block',
    ];
  }
}
