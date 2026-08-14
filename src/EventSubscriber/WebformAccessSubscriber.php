<?php

namespace Drupal\webform_senhaunica\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\webform\WebformInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Uspdev\Senhaunica\Senhaunica;

class WebformAccessSubscriber implements EventSubscriberInterface {

  public function __construct(
    private RouteMatchInterface $routeMatch,
    private MessengerInterface $messenger,
    private Connection $database,
    private LoggerChannelInterface $logger,
  ) {
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 30],
    ];
  }

  public function onRequest(RequestEvent $event): void {

    if (!$event->isMainRequest()) {
      return;
    }

    $route_name = $this->routeMatch->getRouteName();

    // Intercepta apenas a visualização de Webforms.
    if ($route_name !== 'entity.webform.canonical') {
      return;
    }

    $webform = $this->routeMatch->getParameter('webform');

    if (!$webform instanceof WebformInterface) {
      return;
    }

    $habilitado = (bool) $webform->getThirdPartySetting(
      'webform_senhaunica',
      'habilitar_senhaunica',
      FALSE
    );

    if (!$habilitado) {
      return;
    }

    # A partir daqui está habilitado o recurso de senha única
    $config = \Drupal::config('webform_senhaunica.settings');
    $session = \Drupal::request()->getSession();

    $numero_usp = $session->get('senhaunica_numero_usp');
    $nome = $session->get('senhaunica_nome');
    $email = $session->get('senhaunica_email');
    $hash = $session->get('senhaunica_hash');

    if (!empty($numero_usp) && !empty($hash)) {

      $logout_url = Url::fromRoute('webform_senhaunica.logout');

      $logout_link = Link::fromTextAndUrl(
        'Sair',
        $logout_url
      )->toRenderable();

      $logout_link['#attributes'] = [
        'class' => [
          'button--logout',
        ],
      ];

      $this->messenger->addStatus([
        '#markup' => "Você está logado(a) com número USP {$numero_usp} - {$nome}, {$email}. ",
      ]);

      $this->messenger->addStatus($logout_link);
    }

    $session->set('senhaunica_webform_id', $webform->id());
    $session->save();

    if ($session->has('senhaunica_hash')) {
      return;
    }

    // Rota para login com senha única
    putenv("SENHAUNICA_BASE_URL={$config->get('url')}");
    Senhaunica::login();

    //exit;

  }

}
