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

    // Armazenar o webform_id na sessão
    $session->set('senhaunica_webform_id', $webform->id());

    $webform_id = $webform->id();

    // Verificar se o usuário já respondeu este formulário (marcador em sessão)
    $respondidos = $session->get('webform_senhaunica_respondidos', []);
    if (in_array($webform_id, $respondidos)) {
      // Remover mensagem de sucesso anterior
      \Drupal::messenger()->deleteByType('status');
      
      $event->setResponse(
        new RedirectResponse('/ja-respondeu')
      );

      return;
    }

    // Verificar se o usuário já está autenticado
    $numero_usp = $session->get('senhaunica_numero_usp');

    if ($numero_usp) {
      // Usuário já autenticado, verificar se já respondeu no BD
      // (caso de nova sessão ou aba diferente)
      $ja_respondeu = \Drupal::database()
        ->select('webform_senhaunica', 'ws')
        ->fields('ws', ['id'])
        ->condition('numero_usp', $numero_usp)
        ->condition('webform_id', $webform_id)
        ->range(0, 1)
        ->execute()
        ->fetchField();

      if ($ja_respondeu) {
        // Adicionar ao marcador de sessão para futuras verificações
        $respondidos[] = $webform_id;
        $session->set('webform_senhaunica_respondidos', $respondidos);

        // Remover mensagem de sucesso anterior
        \Drupal::messenger()->deleteByType('status');

        $event->setResponse(
          new RedirectResponse('/ja-respondeu')
        );

        return;
      }

      // Usuário autenticado e não respondeu, deixar passar
      \Drupal::logger('webform_senhaunica')->notice(
        'Webform @id acessado por numero_usp @usp (autorizado)',
        ['@id' => $webform_id, '@usp' => $numero_usp]
      );
      return;
    }

    // Usuário não autenticado, redirecionar para o callback (iniciar autenticação)
    \Drupal::logger('webform_senhaunica')->notice(
      'Webform @id acessado - redirecionando para autenticação',
      ['@id' => $webform_id]
    );

    // Remover mensagens anteriores antes de redirecionar
    \Drupal::messenger()->deleteByType('status');

    $event->setResponse(
      new RedirectResponse('/callback')
    );
  }

}
