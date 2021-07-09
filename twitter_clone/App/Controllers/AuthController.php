<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AuthController extends Action {

    /**
     * Verifica se as credenciais de login estão corretas. Caso esteja, o usuário é redirecionado para a timeline 
     * do twitter. Se estiverem erradas, conitnua na tela inicial e um alerta de erro é emitido.
     */
	public function autenticar() {
        $usuario = Container::getModel('Usuario');

        $usuario->__set('email', $_POST['email']);
        $usuario->__set('senha', md5($_POST['senha']));

        $retorno = $usuario->autenticar();
        if(!empty($usuario->__get('id')) && !empty($usuario->__get('nome'))) {
            session_start();

            $_SESSION['id'] = $usuario->__get('id');
            $_SESSION['nome'] = $usuario->__get('nome');

            header('Location: /timeline');
        } else {
            header('Location: /?login=erro');
        }
	}

    /**
     * Finaliza asessão e redireciona para tela inicial de login/cadastro.
     */
    public function sair() {
        session_start();
        session_destroy();
        header('Location: /');
    }
}
?>