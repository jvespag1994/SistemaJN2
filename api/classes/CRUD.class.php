<?php
class CRUD extends DB
{

	public static function CripSenha($senha)
	{
		//$senha = openssl_digest($senha, 'sha512');
		$senha = sha1($senha);
		return $senha;
	}

	private static function Upload($nome_img, $pasta, $renomear, $size = false)
	{
		$imagem = Geral::salva($pasta, $nome_img, $renomear, $size);
		return $imagem;
	}

	private static function UploadMultiplo($valor_file, $pasta, $tabela, $nome_file, $id, $tipo, $campoextra = '')
	{
		$imagem = Geral::SalvaMultiplo($valor_file, $pasta, $tabela, $nome_file, $id, $tipo, $campoextra);
		return $imagem;
	}

	private static function FormataCampos($pasta, $renomear, $tabela, $id, $thumb)
	{
		$values = array();
		$img = array();
		foreach ($_POST as $nome_campo => $valor) {
			$nopost = strripos($nome_campo, 'no_post_');
			if ($nopost === false) {
				if (($nome_campo != 'salvar') && ($nome_campo != 'csenha') && ($nome_campo != 'salva_news')) {
					if (is_array($valor)) {
						$remover = array("");
						$resultado = array_diff($valor, $remover);
						$valor = implode(',', $resultado);
					}
					$values = $values + array($nome_campo => $valor);
				}
			}
		}
		if (isset($_FILES)) {
			foreach ($_FILES as $nome_file => $valor_file) {
				$nopost = strripos($nome_file, 'no_post_');
				if ($nopost === false) {
					if (!is_array($valor_file['name'])) {
						$pasta = ($nome_file == 'thumb') ? $pasta . '/thumb' : $pasta;
						$up_img = self::Upload($nome_file, $pasta, $renomear);

						// if((strpos($pasta, 'uploads')) === false)
						if ($thumb != 0)
							$up_img = self::Upload($nome_file, $pasta . '/thumb', $renomear, $thumb);

						foreach ($valor_file as $key => $value) {
							if (($key == 'name') && ($up_img != '')) {
								if ($id != 0 && $value != '') {
									$del_img = self::DeleteImagem($tabela, $pasta, $id);
								}
								$img = $img + array($nome_file => $up_img);
							}
						}
					}
				}
				$values = $values + $img;
			}
		}
		return $values;
	}

	public static function Insert($tabela, $id = 0, $pasta = '', $renomear = '', $thumb = 0)
	{

		$dados = self::FormataCampos($pasta, $renomear, $tabela, $id, $thumb);

		foreach ($dados as $key => $value) {
			if ($key == 'senha') {
				if ($value != '') {
					$value = self::CripSenha($value);
					$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
				}
			} else if ($key == 'url') {
				if ($id == 0) {
					$var_url = (isset($_POST['titulo'])) ? $_POST['titulo'] : $_POST['nome'];
					$value = Geral::urlamigavelVar($var_url);
					$ok = 1;
					while ($ok >= 1) {
						$value_url = ($value_url == '') ? $value : $value_url;
						if ($id == 0) {
							$verifica = self::SelectOne($tabela, 'url', $value_url);
						} else {
							$verifica = self::SelectTwoMore($tabela, 'url = "' . $value_url . '" AND id != ' . $id);
						}
						if ($verifica['num'] > 0) {
							$value_url = $value . '_' . $ok;
							$ok++;
						} else {
							$ok = 0;
						}
					}
					$formata_dados = $formata_dados . $key . " = '" . $value_url . "', ";
				}
			} else if ((strpos($key, 'url_')) !== false) {
				$value_url = '';
				$post = str_replace('url', '', $key);
				if ($value != '') {
					$value = Geral::urlamigavel($value);
					$ok = 1;
					while ($ok >= 1) {
						$value_url = ($value_url == '') ? $value : $value_url;
						if ($id == 0) {
							$verifica = self::SelectOne($tabela, $key, $value_url);
						} else {
							$verifica = self::SelectTwoMore($tabela, $key . ' = "' . $value_url . '" AND id != ' . $id);
						}
						if ($verifica['num'] > 0) {
							$value_url = $value . '_' . $ok;
							$ok++;
						} else {
							$ok = 0;
						}
					}
					$formata_dados = $formata_dados . $key . " = '" . $value_url . "', ";
				} else {
					$var_url = (isset($_POST['titulo' . $post])) ? $_POST['titulo' . $post] : $_POST['nome' . $post];
					$value = Geral::urlamigavel($var_url);
					$ok = 1;
					while ($ok >= 1) {
						$value_url = ($value_url == '') ? $value : $value_url;
						if ($id == 0) {
							$verifica = self::SelectOne($tabela, $key, $value_url);
						} else {
							$verifica = self::SelectTwoMore($tabela, $key . ' = "' . $value_url . '" AND id != ' . $id);
						}
						if ($verifica['num'] > 0) {
							$value_url = $value . '_' . $ok;
							$ok++;
						} else {
							$ok = 0;
						}
					}
					$formata_dados = $formata_dados . $key . " = '" . $value_url . "', ";
				}
			} else if ($key == 'tags_url') {
				$var_url = $_POST['tags'];
				$value = Geral::urlamigavel($var_url, true);
				$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
			} else if ($key == 'youtube') {
				if ($value != '') {
					$value = Geral::FormataVideo($value);
					$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
				}
			} else if ((strpos($key, 'valor')) !== false) {
				if ($value != '') {
					//$value = Geral::MoedaBD($value);
					$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
				}
			} else if ((strpos($key, 'data')) !== false) {
				if ($value != '') {
					$value = Geral::TransformaData($value);
					$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
				}
			} else
				$formata_dados = $formata_dados . $key . " = '" . $value . "', ";
		}
		$formata_dados = substr($formata_dados, 0, -2);


		if ($id == 0) {
			$insert = self::getConn()->prepare("INSERT INTO {$tabela} SET {$formata_dados}");


			$insert->execute();
			$lastId = self::getConn()->lastInsertId();
			if ($key == 'tags' && $tabela == 'blog') {
				$tag = explode(',', $_POST['tags']);
				$del = self::Delete('blog_blog_tags', 'id_blog', $lastId);
				foreach ($tag as $lista_tag) {
					$var_url = $lista_tag;
					$value = Geral::urlamigavel($var_url, true);
					$ver = self::selectOne('blog_tags', 'url', $value);
					if ($ver['num'] == 0) {
						$formataTags = "tag = '" . $var_url . "', url = '" . $value . "'";
						$insert = self::getConn()->prepare("INSERT INTO blog_tags SET {$formataTags}");
						$insert->execute();
						$lastIdTag = self::getConn()->lastInsertId();
					} else {
						$lastIdTag = $ver['dados'][0]['id'];
					}
					$insert = self::getConn()->prepare("INSERT INTO blog_blog_tags SET id_blog = " . $lastId . ", id_tags = " . $lastIdTag);
					$insert->execute();
				}
			}
		} else {
			$update = self::getConn()->prepare("UPDATE {$tabela} SET {$formata_dados} WHERE `id`=?");
			//echo '<pre>'; print_r ($update); echo '</pre>'; die();
			$update->execute(array($id));
			$lastId = $id;
			if ($key == 'tags' && $tabela == 'blog') {
				$tag = explode(',', $_POST['tags']);
				$del = self::Delete('blog_blog_tags', 'id_blog', $lastId);
				foreach ($tag as $lista_tag) {
					$var_url = $lista_tag;
					$value = Geral::urlamigavel($var_url, true);
					$ver = self::selectOne('blog_tags', 'url', $value);
					if ($ver['num'] == 0) {
						$formataTags = "tag = '" . $var_url . "', url = '" . $value . "'";
						$insert = self::getConn()->prepare("INSERT INTO blog_tags SET {$formataTags}");
						$insert->execute();
						$lastIdTag = self::getConn()->lastInsertId();
					} else {
						$lastIdTag = $ver['dados'][0]['id'];
					}
					$insert = self::getConn()->prepare("INSERT INTO blog_blog_tags SET id_blog = " . $lastId . ", id_tags = " . $lastIdTag);
					$insert->execute();
				}
			}
		}

		if (isset($_FILES['arquivos'])) {
			$up_img = self::UploadMultiplo($_FILES['arquivos'], $pasta . '/pdf', $tabela . '_arquivos', 'id_' . $tabela, $lastId, 'arquivos');
		}

		if (isset($_FILES['imagens'])) {
			$up_img = self::UploadMultiplo($_FILES['imagens'], $pasta . '/fotos', $tabela . '_fotos', 'id_' . $tabela, $lastId, 'fotos', $_POST['no_post_descricao']);
		}


		return $lastId;
	}

	static function SelectAnexo($tabela, $campo, $id)
	{
		$list = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campo} = ? ORDER BY `id` DESC");
		$list->execute(array($id));
		$d['num'] = $list->rowCount();
		$d['dados'] = $list->fetchAll();
		return $d;
	}

	public static function InsertAjax($tabela, $dados)
	{
		$insert = self::getConn()->prepare("INSERT INTO {$tabela} SET {$dados}");
		$insert->execute();
		$lastId = self::getConn()->lastInsertId();
		return $lastId;
	}

	public static function UpdateAjax($tabela, $dados)
	{
		$insert = self::getConn()->prepare("UPDATE {$tabela} SET {$dados}");
		$insert->execute();

		$lastId = self::getConn()->lastInsertId();
		return $lastId;
	}

	public static function Select($tabela, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} ORDER BY {$ordem}");
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll(PDO::FETCH_ASSOC);

		return $d;
	}

	public static function Select2($tabela1, $tabela2, $id)
	{
		$field = 'id_' . $tabela1;
		$select = self::getConn()->prepare("SELECT * FROM {$tabela1} INNER JOIN {$tabela2} ON {$tabela1}.`id` = {$tabela2}.{$field} WHERE {$tabela1}.`id` = ? ORDER BY {$tabela1}.`id` DESC");
		$select->execute(array($id));

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectOne($tabela, $campo, $valor, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campo}=? ORDER BY {$ordem}");
		$select->execute(array($valor));

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectTwoMore($tabela, $campo, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campo} ORDER BY {$ordem}");
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function Delete($tabela, $campo, $id = 0)
	{
		$delete = self::getConn()->prepare("DELETE FROM {$tabela} WHERE {$campo}=?");

		//echo "DELETE FROM {$tabela} WHERE {$campo}={$id}"; die();

		$delete->execute(array($id));

		return $delete;
	}

	public static function SelectJoin($tabela, $inner, $where, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} {$inner} WHERE {$where} ORDER BY {$ordem}");
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectExtra($sql)
	{
		$select = self::getConn()->prepare($sql);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll(PDO::FETCH_ASSOC);

		return $d;
	}

	public static function Truncate($tabela)
	{
		$select = self::getConn()->prepare("TRUNCATE table {$tabela}");
		$select->execute();
	}

	public static function DeleteImagem($tabela, $pasta, $id)
	{
		$img = self::SelectOne($tabela, 'id', $id);
		// return $img['dados'][0]['imagem'];
		unlink($pasta . '/' . $img['dados'][0]['imagem']);
	}

	static function SelectBusca($tabela, $busca, $campo1 = false, $campo2 = false, $campo3 = false, $campo4 = false)
	{
		$campos_busca = ($campo1 != false) ? $campo1 . ' LIKE "%' . $busca . '%"' : $campos_busca;
		$campos_busca = ($campo2 != false) ? $campo2 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo3 != false) ? $campo3 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo4 != false) ? $campo4 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;

		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campos_busca}");
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();
		return $d;
	}

	static function SelectBuscaOne($tabela, $busca, $campo1 = false, $campo2 = false, $campo3 = false, $campo4 = false, $where = false)
	{
		$campos_busca = ($campo1 != false) ? $campo1 . ' LIKE "%' . $busca . '%"' : $campos_busca;
		$campos_busca = ($campo2 != false) ? $campo2 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo3 != false) ? $campo3 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo4 != false) ? $campo4 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($where != false) ? $where . ' AND (' . $campos_busca . ')' : $campos_busca;

		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campos_busca}");
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();
		return $d;
	}

	static function SelectBuscaLimit($tabela, $busca, $limit = 12, $offset = 0, $ordem = 'id DESC', $campo1 = false, $campo2 = false, $campo3 = false, $campo4 = false)
	{
		$campos_busca = ($campo1 != false) ? $campo1 . ' LIKE "%' . $busca . '%"' : $campos_busca;
		$campos_busca = ($campo2 != false) ? $campo2 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo3 != false) ? $campo3 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo4 != false) ? $campo4 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;

		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campos_busca} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();
		return $d;
	}

	static function SelectBuscaLimitOne($tabela, $busca, $limit = 12, $offset = 0, $ordem = 'id DESC', $campo1 = false, $campo2 = false, $campo3 = false, $campo4 = false, $where = false)
	{
		$campos_busca = ($campo1 != false) ? $campo1 . ' LIKE "%' . $busca . '%"' : $campos_busca;
		$campos_busca = ($campo2 != false) ? $campo2 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo3 != false) ? $campo3 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($campo4 != false) ? $campo4 . ' LIKE "%' . $busca . '%" OR ' . $campos_busca : $campos_busca;
		$campos_busca = ($where != false) ? $where . ' AND (' . $campos_busca . ')' : $campos_busca;

		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campos_busca} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();
		return $d;
	}

	public static function SelectLimit($tabela, $limit = 12, $offset = 0, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectLimitOne($tabela, $campo, $valor, $limit = 12, $offset = 0, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campo}= :val ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':val', $valor, PDO::PARAM_INT);
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectLimitTwoMore($tabela, $campo, $limit = 12, $offset = 0, $ordem = 'id DESC')
	{
		$select = self::getConn()->prepare("SELECT * FROM {$tabela} WHERE {$campo} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	public static function SelectLimitJoin($tabela, $inner, $where, $limit = 12, $offset = 0, $ordem = 'id DESC', $campos = '*')
	{
		$select = self::getConn()->prepare("SELECT {$campos} FROM {$tabela} {$inner} WHERE {$where} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();

		return $d;
	}

	static function SelectExtraLimit($sql, $limit = 12, $offset = 0, $ordem = 'id DESC')
	{

		$select = self::getConn()->prepare("{$sql} ORDER BY {$ordem} LIMIT :lim OFFSET :off");
		$select->bindParam(':lim', $limit, PDO::PARAM_INT);
		$select->bindParam(':off', $offset, PDO::PARAM_INT);
		$select->execute();

		$d['num'] = $select->rowCount();
		$d['dados'] = $select->fetchAll();
		return $d;
	}

	public static function CopyPedido($id)
	{
		$copy = self::getConn()->prepare('INSERT INTO pedido (id, id_mp, id_cliente, id_endereco, data, frete, tipo_frete, prazo_frete, valor, status, desconto, cupom, data_entrega, rastreio, pagamento) SELECT id, id, id_cliente, id_endereco, data, frete, tipo_frete, prazo_frete, valor, status, desconto, cupom, data_entrega, rastreio, pagamento FROM pre_pedido WHERE id= ?');
		$copy->execute(array($id));
		$lastId = self::getConn()->lastInsertId();
		$novo_id = $lastId;

		$pega_item = self::SelectOne('pre_pedido_item', 'id_pedido', $id);
		foreach ($pega_item['dados'] as $lista_item) {
			$add = self::CopyItens($lista_item['id'], $novo_id);
		}

		return $novo_id;
	}

	public static function CopyItens($id_item, $id_pedido)
	{
		$down_estoque = 0;
		$copy2 = self::getConn()->prepare('INSERT INTO pedido_item (id_pedido, id_produto, qtd, atributos, valor, tipo) SELECT ?, id_produto, qtd, atributos, valor, tipo FROM pre_pedido_item WHERE id= ?');
		$copy2->execute(array($id_pedido, $id_item));

		$sel_prods = CRUD::SelectOne('pre_pedido_item', 'id', $id_item);
		foreach ($sel_prods['dados'] as $lista_select) {
			if ($lista_select['tipo'] == 0) {
				if ($lista_select['atributos'] != '' && $lista_select['atributos'] != 0) {
					$atts = explode(',', $lista_select['atributos']);
					foreach ($atts as $lista_atributos) {
						$estoque = CRUD::UpdateAjax('produtos_acabamentos', 'estoque = estoque - ' . $lista_select['qtd'] . ' WHERE id_acabamento = ' . $lista_atributos . ' AND id_produto = ' . $lista_select['id_produto']);
					}
				} else {
					$estoque = CRUD::UpdateAjax('produtos', 'estoque = estoque - ' . $lista_select['qtd'] . ' WHERE id = ' . $lista_select['id_produto']);
				}
			} else if ($lista_select['tipo'] == 1) {
				$estoque = CRUD::UpdateAjax('produtos', 'estoque = estoque - ' . $lista_select['qtd'] . ' WHERE id = ' . $lista_select['id_produto']);
			}
		}
	}

	public static function CopyTable($table, $id)
	{
		$monta_fields = array();
		$columns = self::getConn()->prepare("SHOW COLUMNS FROM {$table}");
		$columns->execute();
		$fields = $columns->fetchAll();
		foreach ($fields as $lista_fields) {
			if ($lista_fields['Field'] != 'ID') {
				array_push($monta_fields, $lista_fields['Field']);
			}
		}
		$fields = implode(', ', $monta_fields);

		$copy = self::getConn()->prepare("INSERT INTO {$table} ({$fields}) SELECT {$fields} FROM {$table} WHERE ID= ?");
		$copy->execute(array($id));
		$lastId = self::getConn()->lastInsertId();
		$novo_id = $lastId;

		return $novo_id;
	}

	public static function CopyTableWithId($table, $id, $campo, $newId)
	{
		$monta_fields = array();
		$columns = self::getConn()->prepare("SHOW COLUMNS FROM {$table}");
		$columns->execute();
		$fields = $columns->fetchAll();
		foreach ($fields as $lista_fields) {
			if ($lista_fields['Field'] != 'ID' && $lista_fields['Field'] != $campo) {
				array_push($monta_fields, $lista_fields['Field']);
			}
		}
		$fields = implode(', ', $monta_fields);

		$copy2 = self::getConn()->prepare("INSERT INTO {$table} ({$campo}, {$fields}) SELECT ?, {$fields} FROM {$table} WHERE {$campo}= ?");
		$copy2->execute(array($newId, $id));

		// $estoque = CRUD::UpdateAjax('produtos','estoque = estoque - '.$carrinho['quantidade'].' WHERE id = '.$carrinho['produto']);
	}


	public static function GerarCodigoEmail($id)
	{
		$codigo = time();
		$codigo = base64_encode($codigo);
		$codigo = substr($codigo, 10, 4);
		$codigo = str_pad($codigo, 4, "x");
		$codigo .= base64_encode($id);
		$codigo = base64_encode($codigo);
		return $codigo;
	}

	public static function ReverterCodigoEmail($codigo)
	{
		$id = base64_decode($codigo);
		$id = substr($id, 4);
		$id = base64_decode($id);
		return $id;
	}
}
