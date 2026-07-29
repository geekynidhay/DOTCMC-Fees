<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();
if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == 'save_expense'){
	$save = $crud->save_expense();
	if($save)
		echo $save;
}
if($action == 'delete_expense'){
	$delete = $crud->delete_expense();
	if($delete)
		echo $delete;
}
if($action == 'save_inquiry'){
	$save = $crud->save_inquiry();
	if($save)
		echo $save;
}
if($action == 'get_payments'){
	$get = $crud->get_payments();
	if($get)
		echo $get;
}
if($action == "save_course"){
	$save = $crud->save_course();
	if($save)
		echo $save;
}
if($action == "delete_course"){
	$delete = $crud->delete_course();
	if($delete)
		echo $delete;
}
if($action == "save_student"){
	$save = $crud->save_student();
	if($save)
		echo $save;
}
if($action == "delete_student"){
	$delete = $crud->delete_student();
	if($delete)
		echo $delete;
}
if($action == "resend_link"){
	$resend = $crud->resend_link();
	if($resend)
		echo $resend;
}
if($action == "save_fees"){
	$save = $crud->save_fees();
	if($save)
		echo $save;
}
if($action == "delete_fees"){
	$delete = $crud->delete_fees();
	if($delete)
		echo $delete;
}
if($action == "save_payment"){
	$save = $crud->save_payment();
	if($save)
		echo $save;
}
if($action == "delete_payment"){
	$delete = $crud->delete_payment();
	if($delete)
		echo $delete;
}
if($action == "get_course_fees"){
	$get = $crud->get_course_fees();
	if($get)
		echo $get;
}
ob_end_flush();
?>
