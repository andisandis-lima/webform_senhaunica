<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;
use Uspdev\Senhaunica\Senhaunica;
/**
 * Returns responses for Webform Senha Única routes.
 */
final class Anderson extends ControllerBase {

  /**
   * Builds the response.
   */public function __invoke(): array {


  $clientCredentials = [
    'identifier' => 'identificacao',
    'secret' => 'chave-secreta',
    'callback_id' => 0,
  ];

  
  Senhaunica::login($clientCredentials);

  return [
    '#markup' => 'Olá, ' . Senhaunica::getUserDetail()['nomeUsuario'],
    '#cache' => ['max-age' => 0],
  ];
}
}