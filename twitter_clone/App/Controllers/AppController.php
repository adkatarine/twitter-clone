<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action {

    /**
     * Especifica as todas as informações para a timeline.
     */
	public function timeline() {
        $this->validaAutenticacao();

        $tweet = Container::getModel('Tweet');
        $tweet->__set('id_usuario', $_SESSION['id']);

        // especificações para a paginação.
        $total_registros_pagina = 10;
        $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
        $deslocamento = ($pagina - 1) * $total_registros_pagina;

        $this->view->tweets = $tweet->getPorPagina($total_registros_pagina, $deslocamento);
        $total_tweets = $tweet->getTotalRegistros();
        $this->view->total_de_paginas = ceil($total_tweets['total']/$total_registros_pagina);
        $this->view->pagina_ativa = $pagina;

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);
        $this->view->info_usuario = $usuario->getInfoUsuario();
        $this->view->total_tweets = $usuario->getTotalTweets();
        $this->view->total_seguindo = $usuario->getTotalSeguindo();
        $this->view->total_seguidores = $usuario->getTotalSeguidores();

        $this->render('timeline');
	}

    /**
     * Salva um tweet e 'atualiza' a timeline.
     */
    public function tweet() {
        $this->validaAutenticacao();

        $tweet = Container::getModel('Tweet');
        $tweet->__set('tweet', $_POST['tweet']);
        $tweet->__set('id_usuario', $_SESSION['id']);

        $tweet->salvar();
        header('Location: /timeline');
    }

    /**
     * Verifica se o usuário está logado. É uma segurança caso tentem abrir a timeline direto pelo link sem login.
     */
    private function validaAutenticacao() {
        session_start();
        if(!isset($_SESSION['id']) || empty($_SESSION['id']) || !isset($_SESSION['nome']) || empty($_SESSION['nome'])){
            header('Location: /?login=erro');
        }
    }

    /**
     * Especifica as informações da tela quemSeguir e caso uma pesquisa seja feita, 'retorna' as pessoas com este nome.
     */
    public function quem_seguir() {
        $this->validaAutenticacao();

        $pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';
        $this->view->pesquisarPor = $pesquisarPor;

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);
        $this->view->info_usuario = $usuario->getInfoUsuario();
        $this->view->total_tweets = $usuario->getTotalTweets();
        $this->view->total_seguindo = $usuario->getTotalSeguindo();
        $this->view->total_seguidores = $usuario->getTotalSeguidores();

        $usuarios = array();
        if(!empty($pesquisarPor)) {
            $usuario->__set('nome', $pesquisarPor);
            $usuarios = $usuario->getAll();
        }

        $this->view->usuarios = $usuarios;
        $this->render('quemSeguir');
    }

    /**
     * Verifica se a ação escolhida foi de seguir ou deixar de seguir e a executa.
     */
    public function acao() {
        $this->validaAutenticacao();

        $this->view->pesquisarPor = $pesquisarPor;
        $acao = isset($_GET['acao']) ? $_GET['acao'] : '';
        $id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);

        if($acao == 'seguir') {
            $usuario->seguirUsuario($id_usuario_seguindo);
        } else if($acao == 'deixar_de_seguir') {
            $usuario->deixarSeguirUsuario($id_usuario_seguindo);
        }

        header('Location: /quem_seguir');
    }

    /**
     * Apaga um tweet.
     */
    public function remover() {
        $this->validaAutenticacao();

        $id = isset($_GET['id_tweet']) ? $_GET['id_tweet'] : '';  
        $tweet = Container::getModel('Tweet');
        $tweet->__set('id', $id);

        $tweet->apagarTweet();
        header('Location: /timeline');
    }
}
?> 