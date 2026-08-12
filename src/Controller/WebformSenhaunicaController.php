<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Uspdev\Senhaunica\Senhaunica;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class WebformSenhaunicaController implements ContainerInjectionInterface {
  public function __construct(
    private RequestStack $requestStack,
    private MessengerInterface $messenger
  ) {
  }

  public static function create(ContainerInterface $container): static {
  return new static(
    $container->get('request_stack'),
    $container->get('messenger')
  );
}
  public function __invoke(): RedirectResponse {

  Senhaunica::login();

    /**
     * @var array{
     *   loginUsuario: string,
     *   nomeUsuario: string,
     *   emailPrincipalUsuario?: string,
     *   emailAlternativoUsuario?: string,
     *   emailUspUsuario?: string,
     *   wsuserid: string,
     *   webform_id: string
     * }  $user */
    $user = Senhaunica::getUserDetail();

    $session = $this->requestStack->getSession();

    // Armazenar dados do usuário na sessão para usar na validação
    $session->set('senhaunica_numero_usp', $user['loginUsuario']);
    $session->set('senhaunica_nome_usuario', $user['nomeUsuario']);
    $session->set('senhaunica_email', $user['emailPrincipalUsuario'] ?? $user['emailAlternativoUsuario']
      ?? $user['emailUspUsuario'] ?? '');
    $session->set('senhaunica_hash', $user['wsuserid']);

    $webform_id = $session->get('senhaunica_webform_id');

    if (!is_string($webform_id) || $webform_id === '') {
      throw new \RuntimeException('Webform ID inválido na sessão');
    }

    return new RedirectResponse('/form/' . $webform_id);
  }
}



