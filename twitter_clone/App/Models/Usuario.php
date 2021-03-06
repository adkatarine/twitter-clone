<?php

namespace App\Models;

use MF\Model\Model;

class Usuario extends Model {

    private $id;
    private $nome;
    private $email;
    private $senha;

    public function __get($atributo) {
        return $this->$atributo;
    }

    public function __set($atributo, $valor) {
        $this->$atributo = $valor;
    }

    /**
     * Salva as informações de um novo usuário no banco de dados.
     */
    public function salvar() {
        $query = 'insert into usuarios(nome, email, senha)values(:nome, :email, :senha)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nome', $this->__get('nome'));
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->bindValue(':senha', $this->__get('senha'));
        $stmt->execute();

        return $this;
    }

    /**
     * Verifica se o atributo recebido possui mais de 3 caracteres, retornando true caso tenha.
     * @return boolean
     */
    private function validarQtdCaracter($atributo) {
        return strlen($this->__get($atributo)) < 3;
    }

    /**
     * Verifica a quantidade de caracteres de cada atributo através do método validarQtdCaracter($atributo).
     * @return boolean
     */
    public function validarCadastro() {
        return (self::validarQtdCaracter('nome') or self::validarQtdCaracter('email') or self::validarQtdCaracter('senha')) ? false : true;
    }

    /**
     * Recupera um usuário por email.
     */
    public function getUsuarioPorEmail() {
        $query = "select nome, email from usuarios where email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o usuário já possui cadastro, através do email e senha.
     */
    public function autenticar() {
        $query = 'select id, nome, email from usuarios where email = :email and senha = :senha';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->bindValue(':senha', $this->__get('senha'));
        $stmt->execute();

        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!empty($usuario['id']) && !empty($usuario['nome'])) {
            $this->__set('id', $usuario['id']);
            $this->__set('nome', $usuario['nome']);
        } return $this;
    }

    /**
     * Retorna o(s) usuário(s) pesquisado(s) e se o usuário logado o(s) segue(m) ou não.
     * @return array
     */
    public function getAll() {
        $query = 'select 
            u.id, 
            u.nome, 
            u.email, 
            (
                select
                    count(*) 
                from
                    usuarios_seguidores as us
                where
                    us.id_usuario = :id_usuario and us.id_usuario_seguindo = u.id 
            ) as seguindo_sn
        from 
            usuarios as u
        where 
            u.nome like :nome and u.id != :id_usuario';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nome', '%'.$this->__get('nome').'%');
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva no banco de dados esta relação, quem o usuário logado seguiu.
     */
    public function seguirUsuario($id_usuario_seguindo) {
        $query = 'insert into usuarios_seguidores(id_usuario, id_usuario_seguindo)values(:id_usuario, :id_usuario_seguindo)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
        $stmt->execute();

        return true;
    }

    /**
     * Exclui no banco de dados esta relação, quem o usuário seguia.
     */
    public function deixarSeguirUsuario($id_usuario_seguindo) {
        $query = 'delete from usuarios_seguidores where id_usuario = :id_usuario and id_usuario_seguindo = :id_usuario_seguindo';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
        $stmt->execute();

        return true;
    }

    /**
     * Retorna as informações do usuário logado.
     * @return array
     */
    public function getInfoUsuario() {
        $query = 'select nome from usuarios where id = :id_usuario';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a quantidade de tweets do usuário logado.
     * @return array
     */
    public function getTotalTweets() {
        $query = 'select count(*) as total_tweet from tweets where id_usuario = :id_usuario';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a quantidade de usuários que o usuário logado segue.
     * @return array
     */
    public function getTotalSeguindo() {
        $query = 'select count(*) as total_seguindo from usuarios_seguidores where id_usuario = :id_usuario';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a quantidade de seguidores que o usuário logado possui.
     * @return array
     */
    public function getTotalSeguidores() {
        $query = 'select count(*) as total_seguidores from usuarios_seguidores where id_usuario_seguindo = :id_usuario';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_usuario', $this->__get('id'));
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>