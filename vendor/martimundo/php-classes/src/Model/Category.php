<?php



namespace Martimundo\Model;
use \Martimundo\DB\Sql;
use \Martimundo\Model;

class Category extends Model {

    public static function listAll(){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories ORDER BY idcategory");

        return $results;
    }
    
   public function get($idcategory)
   {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
            ":idcategory"=>$idcategory
        ]);
        $this->setData($results[0]);
   }

    public function delete()
    {   
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$this->getidcategory()
        ));

    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]);
    }
    
}

