<?php

namespace Martimundo\Model;
use \Martimundo\DB\Sql;
use \Martimundo\Model;
use \Martimundo\Mailer;

class User extends Model{

    const SESSION = "User";
    const SECRET = "MRAinPhp7_Secret";
    const SECRET_IV = "MRAinPhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

    

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

    public static function logout()
    {
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll()
    {

        $sql = new Sql();
        $results = $sql->select("SELECT * 
        FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
        return $results;

    }

    public function save()
    {
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            
        ));
        $this->setData($results[0]);

    }
    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select ("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);

    }

    public function update()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            
        ));
        $this->setData($results[0]);
    }
    public function delete()
    {
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser) ", array(
            ":iduser"=>$this->getiduser()
        ));

    }

    public static function getForgot($email, $inadmin = true)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email",array(
            ":email"=>$email
        ));
        if(count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a Senha.");
            
        }else{
            $data = $results[0];

            $recuperasenha = $sql->select ("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
            ":iduser"=>$data["iduser"],
            ":desip"=>$_SERVER['REMOTE_ADDR']
            ));
            if(count($recuperasenha)===0){
                throw new \Exception("Não foi possível recuperar a senha.");
                
            }else{

                $senhaRecuperada = $recuperasenha[0];

                $code = openssl_encrypt($senhaRecuperada['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true) {

					$link = "http://www.lojainfotech.com.br/admin/forgot/reset?code=$code";

				} else {

					$link = "http://www.lojainfotech.com.br/forgot/reset?code=$code";
					
				}				

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da MRA InfoTech", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));				

				$mailer->send();

				return $link;
            }
        }
    }
    public static function validForgotDecrypt($code)
    {
        $code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{

			return $results[0];

        }
        
    }
    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));

    }
    public function setPassword($password)
    {
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()

        ));
    }
}


?>