<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;
use Uspdev\Senhaunica\Senhaunica;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\RedirectResponse;



/**
 * Returns responses for Webform Senha Única routes.
 */
final class WebformSenhaunicaController extends ControllerBase {
    public function __invoke(): array {

  Senhaunica::login();
  $user = Senhaunica::getUserDetail();

  $connection = Database::getConnection();

  $connection->insert('webform_senhaunica')
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
  '#cache' => ['max-age' => 0],
];
  
}
}