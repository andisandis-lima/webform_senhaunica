<?php

namespace Drupal\webform_senhaunica\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\webform\WebformInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;

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

    // Rota para login com senha única
    $config = \Drupal::config('webform_senhaunica.settings');
    putenv("SENHAUNICA_BASE_URL={$config->get('url')}");

    $session = $event->getRequest()->getSession();

    $session->set('senhaunica_webform_id', $webform->id());
  if ($session->has('senhaunica_hash')) {
  return;
}
    $session->save();

    Senhaunica::login();
    exit;

  }

}
