<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller para quando o usuário já respondeu o formulário.
 */
final class AlreadyAnsweredController extends ControllerBase {
  
  public function build(): array {
    return [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--error"><strong>Acesso Negado</strong><p>Você já respondeu este formulário. Não é permitido responder mais de uma vez.</p></div>',
      '#cache' => ['max-age' => 0],
    ];
  }
}
