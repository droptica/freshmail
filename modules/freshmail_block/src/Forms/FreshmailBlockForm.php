<?php

namespace Drupal\freshmail_block\Forms;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
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
      '#title' => t('Email'),
      '#default_value' => $this->currentUser()->getEmail() ? $this->currentUser()->getEmail() : '',
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save',
      '#ajax' => array(
        'callback' => '::submitFormAjaxCallback',
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Getting Random Username',
        ),

      ),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $inputs = $form_state->getUserInput();
    if ($inputs['_drupal_ajax'] == '1') {
      return;
    }
    else {
      if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
        $form_state->setErrorByName('email', $this->t('Invalid e-mail'));
      }
    }
  }

  public function submitFormAjaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $message = '<div class="messages messages--error">' . t('Incorrect E-mail') . '</div>';
      return $response->addCommand(new ReplaceCommand('#freshmail-alert', $message));
    }


    $request = new FreshmailController();
    $freshmail_response = $request->addSubscriber($form_state->getValue('email'));

    if ($freshmail_response['status'] == 'OK') {
      $message = '<div class="messages messages--status">' . t('E-mail add to list') . '</div>';
      return $response->addCommand(new ReplaceCommand('#freshmail-block-form', $message));
    }
    else {
      if(isset($freshmail_response['errors'][0]['message'])) {
        $message = '<div class="messages messages--error">' . t($freshmail_response['errors'][0]['message']) . '</div>';
        return $response->addCommand(new ReplaceCommand('#freshmail-alert', $message));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $request = new FreshmailController();
    $freshmail_response = $request->addSubscriber($form_state->getValue('email'));

    if ($freshmail_response['status'] == 'OK') {
      drupal_set_message(t('E-mail add to list'));
      $form_state->setRebuild();
    }
    else {
      drupal_set_message(t($freshmail_response['errors'][0]['message']), 'error');
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
