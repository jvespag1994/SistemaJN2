<?php
include_once('./api/classes.php');
session_start();
extract($_POST);

$insert = CRUD::Insert('clientes', $_POST['id']);

if ($insert > 0) {
  $_SESSION['msg'] = "<span style='color:green;'>Operação realizada com sucesso!</span>";
} else {
  $_SESSION['msg'] = "<span style='color:red;'>Algo deu errado ;(</span>";
}

header("Location:index.php");
