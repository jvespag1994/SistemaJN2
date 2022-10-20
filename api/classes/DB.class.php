<?php
class DB
{
	private static $conn;
	static function getConn()
	{
		if (is_null(self::$conn)) {
			try {
				self::$conn = connect();
			} catch (PDOException $e) {
				echo 'erro';
			}
		}
		return self::$conn;
	}
}

function connect()
{
	$dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

function logErros($errno)
{

	if (error_reporting() == 0) return;

	$exec = func_get_arg(0);

	$errno = $exec->getCode();
	$errstr = $exec->getMessage();
	$errfile = $exec->getFile();
	$errline = $exec->getLine();
	$err = 'CAUGHT EXCEPTION';

	if (ini_get('log_errrors')) error_log(sprintf("PHP %s: %s in %s on line %d", $err, $errstr, $errfile, $errline));

	$strErro = 'erro: ' . $err . ' no arquivo: ' . $errfile . ' ( linha ' . $errline . ' ) :: IP(' . $_SERVER['REMOTE_ADDR'] . ') data:' . date('d/m/y H:i:s') . "\n";


	$arquivo = fopen('logerro.txt', 'a');
	fwrite($arquivo, $strErro);
	fclose($arquivo);

	set_error_handler('logErros');
}
