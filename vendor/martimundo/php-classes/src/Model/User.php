<?php

namespace Martimundo\Model;
use \Martimundo\DB\Sql;
use \Martimundo\Model;

class User extends Model{

    const SESSION = "User";

    

    public static function login($login, $password)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
        ":LOGIN"=>$login
        ));

        if (count($results)=== 0){
            throw new \Exception("Usuário e senha inexistente ou inválido");
            
        }
        $data = $results[0];

        //verificando se senha é valida
        if (password_verify($password, $data["despassword"]))
        {
            $user = new User();

            $user->setData($data);
            
            //Criando SESSÃO
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
            
        }else{
            throw new \Exception("Usuário e senha inexistente ou inválido");
        }

    }

    //Nesse método é feita a verificação do login do usuário, se esta ou não logado

    public static function verifyLogin($inadmin = true)
    {   //verificar se não existe a sessão
        if(
        !isset($_SESSION[User::SESSION])
        ||
        !$_SESSION[User::SESSION]
        ||
        !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ||
        (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        
        ) {
            header("Location: /admin/login");
            exit;
        }

    }

    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }
}


?>