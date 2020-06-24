<?php

namespace Martimundo;

use Rain\Tpl;

class Page{

    private $tpl;
    private $options;
    private $defautls = [
        "header"=>true,
		"footer"=>true,
		"data"=>[]
    ];

    //Método para Criar o header de todas as páginas
    public function __construct($opts = array()){
        $this->options=array_merge($this->defautls, $opts);

        // configuranto exemplo com o Tamplet RainTpl...
	    $config = array(
        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
        "debug"         => false // set to false to improve the speed
       );

        Tpl::configure( $config );

        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);

        $this->tpl->draw("header");

    }

    private function setData($data = array()){
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    //Criando a função para a criação das paginas de conteudos
    public function setTpl($name, $data = array(), $returnHTML = false){
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }
    //Método para criar o Footer das páginas
    public function __destruct(){

        $this->tpl->draw("footer");

    }
}

?>