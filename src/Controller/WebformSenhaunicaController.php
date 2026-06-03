<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;
use Uspdev\Senhaunica\Senhaunica;
use Drupal\Core\Database\Database;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Webform Senha Única routes.
 */
final class WebformSenhaunicaController extends ControllerBase {
    public function __invoke(): array {

    //$config = $this->config('webform_senhaunica.settings');


  $clientCredentials = [
    'identifier' => 'identificacao',
    'secret' => 'chave-secreta',
    'callback_id' => 0,
  ];

  Senhaunica::login($clientCredentials);

  $user = Senhaunica::getUserDetail();

  

  Database::getConnection()
    ->insert('webform_senhaunica')
    ->fields([
      'numero_usp' => $user['loginUsuario'],
      'nome_usuario' => $user['nomeUsuario'],
      'email' => ($user['emailPrincipalUsuario'] ?? '')
        ?: ($user['emailAlternativoUsuario'] ?? '')
        ?: ($user['emailUspUsuario'] ?? ''),
      'hash' => $user['wsuserid'],
      'created' => time(),
    ])
    ->execute();

  return [
    '#markup' => 'Login realizado com sucesso',
  ];
}
}
