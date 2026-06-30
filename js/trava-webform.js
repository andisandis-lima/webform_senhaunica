(function (window, document) {
  'use strict';

  let permitirSaida = false;

  // Escuta nativamente a tentativa de fechar ou recarregar a aba
  window.addEventListener('beforeunload', function (e) {
    // Só bloqueia se o formulário do webform estiver presente na tela e o envio não tiver sido acionado
    if (!permitirSaida && document.querySelector('form.webform-submission-form')) {
      e.preventDefault();
      e.returnValue = ''; // Dispara a caixa de diálogo padrão do navegador
    }
  });

  // Aguarda o formulário carregar para mapear o botão de envio
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.webform-submission-form');
    if (form) {
      form.addEventListener('submit', function () {
        permitirSaida = true; // Libera a saída do usuário quando ele envia o formulário
      });
    }
  });

})(window, document);