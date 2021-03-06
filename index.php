<?php 

session_start();

require_once("vendor/autoload.php");
use \Slim\Slim;
use \Martimundo\Page;
use \Martimundo\PageAdmin;
use \Martimundo\Model\User;
use \Martimundo\Model\Category;

$app = new Slim();

$app->config('debug', true);
/**********************************************************************************************************
 * ROTAS DE ACESSO VIA GET
 */
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

//essas rotas acessam os dados
$app->get ('/admin/users', function (){
	
	User::verifyLogin();

	$users = User::listAll();//Metodo para trazer os dados os usuários	
	
	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));
});
$app->get('/admin/users/create', function (){
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

$app->get('/admin/users/:iduser/delete', function($iduser){
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header('Location: /admin/users');
	exit;


});

$app->get('/admin/users/:iduser', function($iduser){
	
	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$page = new PageAdmin();	
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});
$app->get('/admin/forgot', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});
$app->get('/admin/forgot/sent', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");
});

$app->get('/admin/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET["code"]

	));
});
$app->get('/admin/categories', function(){
	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$categories
	]);


});
$app->get('/admin/categories-create', function(){
	User::verifyLogin();	

	$page = new PageAdmin();
	$page->setTpl("categories-create");



});

$app->get('/admin/categories/:idcategory/delete', function($idcategory){

	User::verifyLogin();

	$categories = new Category();

	$categories->get((int)$idcategory);

	$categories->delete();

	header('Location: /admin/categories');	exit;


});
$app->get('/admin/categories/:idcategory', function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();
		
	$page->setTpl("categories-update", array(
		"category"=>$category->getValues()
	));

});

/***************************************************************************************************
 * ROTAS DE ACESSO VIA POST
 */
//as rotas abaixo farão o envio dos dados ao banco de dados.
$app->post("/admin/users/create", function () {

	User::verifyLogin();
	
   	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);
	$user->setData($_POST);
	$user->save();
	header('Location: /admin/users');
	exit;

});

$app->post('/admin/users/:iduser', function($iduser){
	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();
	
	header('Location: /admin/users');
	exit;

});
$app->post('/admin/forgot', function(){


	$user = User::getForgot($_POST["email"]);

	header('Location: /admin/forgot/sent');
	exit;

});
$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

$app->post('/admin/categories/create', function(){

	User::verifyLogin();

	$categoria = new Category();

	$categoria->setData($_POST);

	$categoria->save();

	header('Location: /admin/categories');
	exit;

});
$app->post('/admin/categories/:idcategory', function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();
	
	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});


$app->run();

 ?>