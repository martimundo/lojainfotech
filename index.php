<?php 

session_start();

require_once("vendor/autoload.php");
use \Slim\Slim;
use \Martimundo\Page;
use \Martimundo\PageAdmin;
use \Martimundo\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
		
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
});

$app->post('/admin/login', function(){

	//Essa dados pegaram os dados via post através do method action do formulário
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function(){

	User::logout();

	header('Location: /admin/login');
	exit;
});

$app->run();

 ?>