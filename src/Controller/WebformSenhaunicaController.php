<?php

declare(strict_types=1);

namespace Drupal\webform_senhaunica\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Webform Senha Única routes.
 */
final class WebformSenhaunicaController extends ControllerBase {
    public function __invoke(): array {
        // die('To aqui galera');

        $user = Uspdev\Senhaunica\Senhaunica::getUserDetail();

        $data = $webform_submission->getData();

        $data['wsuserid']     = 12345;
        $data['loginUsuario'] = 'usuario_teste';
        $data['nomeUsuario']  = 'Nome de Exemplo';

        $webform_submission->setData($data);
        $webform_submission->save();

        $build['content'] = [
            '#type' => 'item',
            '#markup' => $this->t('It works!'),
        ];

        return $build;
    }
}
