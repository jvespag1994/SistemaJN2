<?php
session_start();
include("./api/classes/config.php");
include("./api/classes/DB.class.php");
include("./api/classes/Geral.class.php");
include("./api/classes/CRUD.class.php");
extract($_POST);

$resposta = array(
  'status' => '',
  'title' => '',
  'msg' => '',
);

try {
  $del_quest = CRUD::Delete($tabela, 'ID', $id);
  if (!empty($del_quest)) {
    $resposta['title'] = "Feito!";
    $resposta['status'] = "success";
    $resposta['msg'] = "<p style='color:green;'>Registro removido com sucesso!</p>";
  } else {
    $resposta['title'] = "Falha!";
    $resposta['status'] = "error";
    $resposta['msg'] = "<p style='color:red;'>Algo deu errado.. Tente novamente!</p>";
  }
} catch (Exception $e) {
  $resposta['title'] = "Falha!";
  $resposta['status'] = "error";
  $resposta['msg'] = "<p style='color:red;'>Algo deu errado.. Este registro possui vínculos...</p>";
}

header('Content-Type: application/json');
echo json_encode($resposta);
