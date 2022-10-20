<?php include_once('./api/classes.php');
session_start(); ?>

<link rel="stylesheet" href="./style.css">

<body cz-shortcut-listen="true">
  <main role="main">
    <h1><a href="index.php">Controle de Clientes</a></h1>

    <div class="row">
      <div class="col-lg-6 mb-2">
        <form action="index.php" method="GET" class="d-flex">
          <input class="form-control" name="busca" type="text" placeholder="Buscar pelo final da placa">
          <button type="submit" class="btn"><i class="fa fa-search"></i></button>
        </form>
      </div>
    </div>

    <table>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Telefone</th>
        <th>CPF</th>
        <th>Placa do Carro</th>
        <th>Editar</th>
        <th>Deletar</th>
      </tr>
      <tbody>
        <?php
        if (isset($_GET['busca']) && ($_GET['busca'] != '')) {
          $clients = CRUD::SelectExtra("SELECT * FROM clientes WHERE placa_veiculo LIKE '%{$_GET['busca']}%'");
        } else {
          $clients = CRUD::Select('clientes', 'id');
        }
        foreach ($clients['dados'] as $final) {
        ?>
          <tr>
            <td><?php echo $final['id']; ?></td>
            <td><?php echo $final['nome']; ?></td>
            <td><?php echo $final['telefone']; ?></td>
            <td><?php echo $final['CPF']; ?></td>
            <td><?php echo $final['placa_veiculo']; ?></td>

            <td><a href="clientes.php?id=<?php echo $final['id']; ?>"><i class="fa fa-cog"></i></a></td>
            <td>
              <button type="button" data-toggle="modal" data-target="#confirm-delete" class="confirm-delete" data-toggle="tooltip" data-original-title="Deletar" data-id="<?php echo $final['id']; ?>" data-tabela="clientes" data-local="index">
                <i class="far fa-trash-alt trash" aria-hidden="true" data-target="#"></i>
              </button>

            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php
    if (isset($_SESSION['msg'])) {
      echo $_SESSION['msg'];
      $_SESSION['msg'] = '';
    }
    ?><br>
    <a href="clientes.php"><button class="btn btn-primary mt-2">Novo Cadastro</button></a>
  </main>
</body>

<!-- Request via modal  -->
<div id="confirm-delete" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
  </div>
</div>


<script>
  // REQUEST DELETAR ITENS
  $(".confirm-delete").click(function() {
    const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: "btn btn-danger",
        cancelButton: "btn btn-secondary",
      },
      buttonsStyling: false,
    });

    swalWithBootstrapButtons
      .fire({
        title: "Você tem certeza? ",
        text: "Assim que remover este registro, não tera como recupera-lo",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, deletar!",
        cancelButtonText: "Não, cancelar!",
        reverseButtons: true,
      })
      .then((result) => {
        if (result.value) {
          var id = $(this).data("id");
          var tabela = $(this).data("tabela");
          var local = $(this).data("local");
          var idretorno = $(this).data("idretorno");
          $.ajax({
            type: "POST",
            url: "deletar.php",
            data: {
              id: id,
              tabela: tabela,
              local: local,
              idretorno: idretorno,
            },
            success: function(resposta) {
              if (resposta.status == "success") {
                window.location.reload()
              }

              swalWithBootstrapButtons.fire(
                resposta.title,
                resposta.msg,
                resposta.status,
              );
            },
          });
        }
      });
  });
</script>