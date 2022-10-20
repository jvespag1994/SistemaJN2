<?php
class Geral extends DB
{

	static function TransformaData($data)
	{
		$data = substr($data, 6, 4) . '-' . substr($data, 3, 2) . '-' . substr($data, 0, 2);
		return $data;
	}

	static function TransformaData2($data)
	{
		$data = substr($data, 4, 4) . '-' . substr($data, 2, 2) . '-' . substr($data, 0, 2);
		return $data;
	}

	static function urlamigavel($url)
	{

		$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
		$b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
		$url = utf8_decode($url);
		$url = strtr($url, utf8_decode($a), $b);
		$url = strtolower($url);
		$url = utf8_encode($url);
		$url = strip_tags($url);
		$url = preg_replace("/[^a-zA-Z0-9_]/", " ", $url);

		$url = str_replace(" ", "-", $url);
		return $url;
	}

	static function salva($pasta, $nome_img, $renomear, $size = false)
	{
		// $handle = new \Verot\Upload\Upload($_FILES[$nome_img]);
		$handle = new Upload($_FILES[$nome_img]);
		if ($handle->uploaded) {
			// $img = 0;
			//$handle->image_resize = true;
			//$handle->image_ratio_y = false;
			//$handle->image_x = 640;
			//$handle->image_y = 350;
			//$handle->image_watermark = 'watermark.png';
			//$handle->image_watermark_x = -1;
			//$handle->image_watermark_y = -1;
			if ($size != false) {
				$handle->image_resize = true;
				// $handle->image_ratio_pixels = $size;
				$handle->image_x = $size;
				$handle->image_ratio_y = true;
				// $handle->image_watermark_x = -1;
				// $handle->image_watermark_y = -1;
			}
			if ($renomear == 'yes') {
				$handle->file_overwrite = true;
				$handle->file_auto_rename = false;
				$handle->file_new_name_body = $nome_img;
			}
			if ($renomear == 'no') {
				$handle->file_overwrite = false;
				$handle->file_auto_rename = true;
			}
			if ($renomear == 'random') {
				$handle->file_overwrite = false;
				$handle->file_auto_rename = true;
				$handle->file_new_name_body = md5(rand() * 10000000);
			}
			$handle->jpeg_quality = 100;
			$handle->mime_check = true;
			$handle->allowed = array('image/*', 'application/pdf');
			//$handle->file_max_size = '1048576';

			$handle->Process($pasta);

			if ($handle->processed) {
				$novaimagem = $handle->file_dst_name;
				return $novaimagem;
			} else {
				echo '<fieldset>';
				echo ' <legend>Erro encontrado!</legend>';
				echo ' Erro: ' . $handle->error . '';
				echo '</fieldset>';
			}
			$handle->Clean();
		}
	}

	static function SendMail($subject, $altbody, $message, $to, $toName, $anexo = false, $from = null, $FromName = null)
	{
		$mail = new PHPMailer();

		// Servidor
		$mail->isSMTP();
		$mail->SMTPDebug 	= false;
		$mail->Host 		= MAIL_HOST;
		$mail->SMTPAuth 	= true;
		$mail->Username 	= MAIL_USER;
		$mail->Password 	= MAIL_PASS;
		$mail->Port 		= MAIL_PORT;
		$mail->SMTPSecure 	= MAIL_SECURE;

		if ($from == null) {
			// Remetente
			$mail->From 		= MAIL_SEND;
			$mail->FromName 	= SITE_NAME;
		} else {
			// Remetente
			$mail->From 		= $from;
			$mail->FromName 	= $FromName;
		}

		// Destino
		$mail->addAddress($to, $toName);

		// Dados da Mensagem
		$mail->isHTML(true);
		$mail->CharSet 	= 'utf-8';
		$mail->WordWrap = 70;

		// Mensagem
		$mail->Subject 	= $subject;
		$mail->Body 	= $message;
		$mail->AltBody 	= strip_tags($altbody);
		if ($anexo != false) {
			$mail->AddAttachment($anexo);
		}

		if (!$mail->send()) {
			return $mail->ErrorInfo;
		} else {
			return '1';
		}
	}

	static function StatusTable($table, $id, $status, $campo)
	{
		$up = self::getConn()->prepare('UPDATE ' . $table . ' SET ' . $campo . '=? WHERE `id`=?');
		$up->execute(array($status, $id));
	}

	static function PagamentoTable($table, $id, $status)
	{
		$up = self::getConn()->prepare('UPDATE ' . $table . ' SET `pagamento`=? WHERE `id`=?');
		$up->execute(array($status, $id));
	}

	static function DelDado($id, $table)
	{
		$up = self::getConn()->prepare('DELETE FROM ' . $table . ' WHERE `id`=?');
		$up->execute(array($id));
	}

	static function DelFoto($pasta, $foto)
	{
		unlink($pasta . $foto);
	}

	static function AlteraOrdem($table, $page, $id, $ordem)
	{
		$update = self::getConn()->prepare('UPDATE ' . $table . ' SET `ordem` = ? WHERE `id`=?');
		$update->execute(array($ordem, $id));
		// return $update;
	}

	static function FormataVideoYoutube($video)
	{
		$video = explode('v=', $video);
		$video = substr($video[1], 0, 11);
		return $video;
	}

	static function FormataVideoVimeo($video)
	{
		$video = explode('/', $video);
		// $video = substr($video[1], 0, 11);
		$vimeo = array_pop($video);
		return $vimeo;
	}

	static function MontarLink($texto)
	{
		if (!is_string($texto))
			return $texto;
		$er = "/(http(s)?:\/\/(www|.*?\/)?((\.|\/)?[a-zA-Z0-9&%_?=-]+)+)/i";
		preg_match_all($er, $texto, $match);

		foreach ($match[0] as $link) {
			// $link = strtolower($link);
			$link_len = strlen($link);

			//troca "&" por "&amp;", tornando o link válido pela W3C
			$web_link = str_replace("&", "&amp;", $link);

			$texto = str_ireplace($link, "<a href=\"" . $web_link . "\" target=\"_blank\" title=\"" . $web_link . "\" rel=\"nofollow\">" . (($link_len > 60) ? substr($web_link, 0, 25) . "..." . substr($web_link, -15) : $web_link) . "</a>", $texto);
		}

		return $texto;
	}

	static function display_menus($parent_id = 0, $url_site, $field)
	{
		$menu = Page::SelectParentMenu($parent_id);
		if ($parent_id == 0) {
			$ul = '<ul class="submobile">';
		} else {
			$ul = '<ul class="submenu">';
		}
		$html = $ul;
		foreach ($menu as $lista) {
			if ($parent_id == 0) {
				$html .= "<li><a href=" . $url_site . 'produtos/' . $lista['link' . $field] . "><strong>" . $lista['titulo' . $field] . "</strong></a>";
			} else {
				$html .= "<li><a href=" . $url_site . 'produtos/' . $lista['link' . $field] . "><strong>" . $lista['titulo' . $field] . "</strong></a></li>";
			}
			$html .= self::display_menus($lista['id'], $url_site, $field);
			$html .= "</li>";
		}
		$html .= "</ul>";

		return $html;
	}

	static function diferencaEntreDatas($data_inicial, $data_final)
	{
		$time_inicial = self::geraTimestamp($data_inicial);
		$time_final = self::geraTimestamp($data_final);
		$diferenca = $time_final - $time_inicial;
		$dias = (int)floor($diferenca / (60 * 60 * 24));
		return $dias;
	}

	static function geraTimestamp($data)
	{
		$partes = explode('/', $data);
		return mktime(0, 0, 0, $partes[1], $partes[0], $partes[2]);
	}

	static function novaSenha($qtd)
	{
		$caracteres = 'abcdefghijklmnopqrstuvwxyz1234567890';
		$nova_senha = substr(str_shuffle($caracteres), 0, $qtd);
		return $nova_senha;
	}

	static function geraNumeroDaSorte($letras, $numeros)
	{
		for ($i = 0; $i < $numeros; $i++) {
			$qtdNumeros .= 9;
		}
		$insereNumeros = "%0" . $numeros . "d";
		$numero_da_sorte = rand(0, $qtdNumeros);
		$numero_da_sorte = sprintf($insereNumeros, $numero_da_sorte);
		$Caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$QuantidadeCaracteres = strlen($Caracteres);
		$QuantidadeCaracteres--;
		$Hash = NULL;
		for ($x = 1; $x <= $letras; $x++) {
			$Posicao = rand(0, $QuantidadeCaracteres);
			$Hash .= substr($Caracteres, $Posicao, 1);
		}
		return $Hash . '-' . $numero_da_sorte;
	}
}
