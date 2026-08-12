<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Webform Senha Única settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'webform_senhaunica_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['webform_senhaunica.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
  $config = $this->config('webform_senhaunica.settings');

  $form['identifier'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Identificador'),
    '#default_value' => $config->get('identifier'),
    '#required' => TRUE,
  ];

  $form['secret'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Secret'),
    '#default_value' => $config->get('secret'),
    '#required' => TRUE,
  ];

  $form['callback_id'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Callback ID'),
    '#default_value' => $config->get('callback_id'),
    '#required' => TRUE,
  ];

   $form['url'] = [
    '#type' => 'textfield',
    '#title' => $this->t('URL'),
    '#default_value' => $config->get('url'),
    '#required' => TRUE,
    '#description' => 'http://auth.local:3141/wsusuario/oauth'
  ];

  return parent::buildForm($form, $form_state);
}

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state): void {

 \Drupal::logger('webform_senhaunica')
    ->notice('submitForm executado');
    
  $this->config('webform_senhaunica.settings')
    ->set('identifier', $form_state->getValue('identifier'))
    ->set('secret', $form_state->getValue('secret'))
    ->set('callback_id', $form_state->getValue('callback_id'))
    ->set('url', $form_state->getValue('url'))
    ->save();

  parent::submitForm($form, $form_state);
}

}
