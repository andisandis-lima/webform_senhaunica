<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;
use Uspdev\SenhaunicaEasy\ServerUSP;
use Symfony\Component\HttpFoundation\Request;
use Uspdev\Senhaunica\Senhaunica;

/**
 * Returns responses for Webform Senha Única routes.
 */
final class Anderson extends ControllerBase {
   /**
 * @return array<string, mixed>
 */
   public function __invoke(): array {
    
    Senhaunica::logout();

    $clientCredentials = [
    'identifier' => 'identificacao',
    'secret' => 'chave-secreta',
    'callback_id' => 0,
];

    Senhaunica::login();

  }
}

