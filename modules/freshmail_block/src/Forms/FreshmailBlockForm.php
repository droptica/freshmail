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
      '#title' => $this->t('Email'),
      '#default_value' => \Drupal::currentUser()
        ->getEmail() ? \Drupal::currentUser()->getEmail() : '',
    );

    $form['submit'] = array(
      '#type' => 'button',
      '#value' => 'Save',
      '#ajax' => array(
        'callback' => '::submitFormAjaxCallback',
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => $this->t('Adding E-mail...'),
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
    if (isset($inputs['_drupal_ajax']) && ($inputs['_drupal_ajax'] == '1')) {
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

    // Validation.
    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $message = '<div id="freshmail-alert"><div class="messages messages--error">' . $this->t('Incorrect E-mail') . '</div></div>';
      return $response->addCommand(new ReplaceCommand('#freshmail-alert', $message));
    }

    // Submit.
    $request = new FreshmailController();
    $freshmail_response = $request->addSubscriber($form_state->getValue('email'));

    if ($freshmail_response['status'] == 'OK') {
      $message = '<div class="messages messages--status">' . $this->t('E-mail added to list') . '</div></div>';
      return $response->addCommand(new ReplaceCommand('#freshmail-block-form', $message));
    }


    if (isset($freshmail_response['errors'][0]['message'])) {
      $message = $this->t($freshmail_response['errors'][0]['message']);
    }
    else {
      $message = $this->t('Freshmail error - please contact with administrator');
    }

    $message = '<div id="freshmail-alert"><div class="messages messages--error">' . $message . '</div></div>';
    return $response->addCommand(new ReplaceCommand('#freshmail-alert', $message));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

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
