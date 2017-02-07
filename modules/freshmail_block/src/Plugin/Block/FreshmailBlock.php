<?php

namespace Drupal\freshmail_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Freshmail system block.
 *
 * @Block(
 *   id = "freshmail_block",
 *   admin_label = @Translation("Freshmail block")
 * )
 */
class FreshmailBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'freshmail_block_label_value' => $this->t('Add to list'),
      'freshmail_block_submit_value' => $this->t('Subscribe'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['freshmail_block_label_value'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Field label'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['freshmail_block_label_value'],
    );
    $form['freshmail_block_submit_value'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Button label'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['freshmail_block_submit_value'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['freshmail_block_label_value']
      = $form_state->getValue('freshmail_block_label_value');
    $this->configuration['freshmail_block_submit_value']
      = $form_state->getValue('freshmail_block_submit_value');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\freshmail_block\Forms\FreshmailBlockForm');

    if (isset($this->configuration['freshmail_block_label_value'])) {
      $form['email']['#title'] = $this->configuration['freshmail_block_label_value'];
    }
    if (isset($this->configuration['freshmail_block_submit_value'])) {
      $form['submit']['#value'] = $this->configuration['freshmail_block_submit_value'];
    }

    return array(
      'freshmail_form' => $form,
    );
  }
}
