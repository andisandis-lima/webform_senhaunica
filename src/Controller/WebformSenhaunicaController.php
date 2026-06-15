<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;
use Uspdev\Senhaunica\Senhaunica;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Webform Senha Única routes.
 */
final class WebformSenhaunicaController extends ControllerBase {
  public function __invoke(): array|Response {
    
    Senhaunica::login();
    $user = Senhaunica::getUserDetail();

    $session = \Drupal::request()->getSession();

    // Armazenar dados do usuário na sessão para usar na validação
    $session->set('senhaunica_numero_usp', $user['loginUsuario']);
    $session->set('senhaunica_nome_usuario', $user['nomeUsuario']);
    $session->set('senhaunica_email', ($user['emailPrincipalUsuario'] ?? '')
      ?: ($user['emailAlternativoUsuario'] ?? '')
      ?: ($user['emailUspUsuario'] ?? ''));
    $session->set('senhaunica_hash', $user['wsuserid']);

    $webform_id = $session->get('senhaunica_webform_id');

    \Drupal::messenger()->addStatus(
      t('Login realizado com sucesso. Você pode responder o formulário.')
    );

    // Redirecionar para o webform para responder
    return new RedirectResponse("/webform/$webform_id");
  }
}



