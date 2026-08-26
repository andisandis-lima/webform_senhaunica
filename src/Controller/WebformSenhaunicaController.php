<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Uspdev\Senhaunica\Senhaunica;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Url;

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

  /**
   * Página exibida depois que o usuário sai.
   */
  public function login(): array {

    $url = Url::fromRoute('webform_senhaunica.callback')->toString();

    return [
      '#markup' => '
        <div style="
          text-align:center;
          padding:40px 20px;
        ">
          <p style="
            margin-bottom:20px;
            font-size:16px;
          ">
            Você saiu da Senha Única.
          </p>

          <a href="' . $url . '" style="
            display:inline-block;
            padding:7px 12px;
            background:#077282;
            color:#fff;
            text-decoration:none;
            border-radius:4px;
            font-size:14px;
          ">
            Entrar novamente com Senha Única
          </a>
        </div>
      ',
    ];
  }

  /**
   * Callback da Senha Única.
   */
  public function __invoke(): RedirectResponse {

    $config = \Drupal::config('webform_senhaunica.settings');

    $identifier = trim($config->get('identifier'));
    $secret = trim($config->get('secret'));
    $url = trim($config->get('url'));
    $callback_id = trim($config->get('callback_id'));

    putenv("SENHAUNICA_KEY={$identifier}");
    putenv("SENHAUNICA_SECRET={$secret}");
    putenv("SENHAUNICA_BASE_URL={$url}");
    putenv("SENHAUNICA_CALLBACK_ID={$callback_id}");
    Senhaunica::login();

    /**
     * @var array{
     *   loginUsuario: string,
     *   nomeUsuario: string,
     *   emailPrincipalUsuario?: string,
     *   emailAlternativoUsuario?: string,
     *   emailUspUsuario?: string,
     *   wsuserid: string,
     *   submission_id: string
     * } $user
     */
    $user = Senhaunica::getUserDetail();

    $session = $this->requestStack->getSession();

    // Salva os dados do usuário na sessão.
    $session->set(
      'senhaunica_numero_usp',
      $user['loginUsuario']
    );

    $session->set(
      'senhaunica_nome',
      $user['nomeUsuario']
    );

    $session->set(
      'senhaunica_email',
      $user['emailPrincipalUsuario']
      ?? $user['emailAlternativoUsuario']
      ?? $user['emailUspUsuario']
      ?? ''
    );

    $session->set(
      'senhaunica_hash',
      $user['wsuserid']
    );

    $webform_id = $session->get('senhaunica_webform_id');

    if (!is_string($webform_id) || $webform_id === '') {
      throw new \RuntimeException(
        'Webform ID inválido na sessão'
      );
    }

    $webform = Webform::load($webform_id);

    if (!$webform) {
      throw new \RuntimeException(
        'Webform não encontrado'
      );
    }

    $url = $webform->toUrl()->toString();

    return new RedirectResponse($url);
  }

  /**
   * Logout da Senha Única.
   */
  public function logout(): RedirectResponse {

    $session = $this->requestStack->getSession();

    Senhaunica::logout();

    // Guarda o Webform atual.
    $webform_id = $session->get('senhaunica_webform_id');

    // Faz logout da sessão própria da Senha Única.
    Senhaunica::logout();

    // Limpa os dados da Senha Única armazenados pelo Drupal.
    $session->remove('senhaunica_numero_usp');
    $session->remove('senhaunica_nome');
    $session->remove('senhaunica_email');
    $session->remove('senhaunica_hash');

    // Mantém o Webform para o próximo login.
    if (is_string($webform_id) && $webform_id !== '') {
      $session->set('senhaunica_webform_id', $webform_id);
    }

    $session->save();

    // Mostra a tela para entrar novamente.
    $url = Url::fromRoute(
      'webform_senhaunica.login'
    )->toString();

    return new RedirectResponse($url);
  }
}
