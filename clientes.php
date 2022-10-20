<?php include_once('./api/classes.php');
session_start();
error_reporting(0);

if ($_GET == true) {
  extract($_GET);
  $id = $_GET['id'];

  $user = CRUD::SelectOne('clientes', 'id', $id);
  $idUser = $user['dados'][0]['id'];
  $nameUser = $user['dados'][0]['nome'];
  $telUser = $user['dados'][0]['telefone'];
  $cpfUser = $user['dados'][0]['CPF'];
  $placaUser = $user['dados'][0]['placa_veiculo'];
}
?>

<section class="ml-2">
  <?php if (!$_GET) {
    echo '<h1>Novo Cliente</h1>';
  } else {
    echo '<h1>Cliente #' . $idUser . ' - ' . $nameUser . '</h1>';
  }
  ?>

  <form method="POST" action="./submit.php">
    <div class="row">
      <div class="col-lg-4 col-sm-12">
        <?php if (!$_GET) { ?>
          <label for="tel">Nome Cliente:</label>
          <input id="tel" class="form-control" type="text" name="nome" value="">
          <hr>
        <?php } ?>

        <label for="tel">Telefone:</label>
        <input id="tel" class="form-control" type="text" name="telefone" value="<?php echo $telUser; ?>" placeholder="Ex. (00) 00000-0000" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);">
        <hr>
        <label for="cpf">CPF:</label>
        <input id="cpf" class="form-control" type="text" name="CPF" value="<?php echo $cpfUser; ?>" placeholder="Ex. 000.000.000-00">
        <hr>
        <label for="placa">Placa do Veículo:</label>
        <input id="placa" class="form-control" type="text" name="placa_veiculo" value="<?php echo $placaUser; ?>" placeholder="Ex. AAAA-0000" maxlength="9">

        <input type="hidden" name="id" value="<?php echo $idUser; ?>">
        <?php
        if (isset($_SESSION['msg'])) {
          echo $_SESSION['msg'];
          $_SESSION['msg'] = '';
        }
        ?>
        <br>
        <button class="btn btn-success mt-3">Submit</button>
      </div>
    </div>
  </form>
</section>


<script type="text/javascript">
  // Mascara de CPF
  $(document).ready(function() {
    $("#cpf").mask("999.999.999-99");
  });


  // Mascara de Telefone
  function mask(o, f) {
    setTimeout(function() {
      var v = mphone(o.value);
      if (v != o.value) {
        o.value = v;
      }
    }, 1);
  }

  function mphone(v) {
    var r = v.replace(/\D/g, "");
    r = r.replace(/^0/, "");
    if (r.length > 10) {
      r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
    } else if (r.length > 5) {
      r = r.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
    } else if (r.length > 2) {
      r = r.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
    } else {
      r = r.replace(/^(\d*)/, "($1");
    }
    return r;
  }
</script>