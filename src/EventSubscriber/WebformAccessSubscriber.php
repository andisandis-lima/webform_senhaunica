<?php

namespace Drupal\webform_senhaunica\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class WebformAccessSubscriber implements EventSubscriberInterface {

  protected RouteMatchInterface $routeMatch;

  public function __construct(RouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
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

    if (!$webform) {
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

    $session = $event->getRequest()->getSession();

    $session->set('senhaunica_webform_id', $webform->id());

    \Drupal::logger('webform_senhaunica')->notice(
      'Webform @id salvo na sessao',
      ['@id' => $webform->id()]
    );

    $event->setResponse(
      new RedirectResponse('/anderson')
    );
  }

}