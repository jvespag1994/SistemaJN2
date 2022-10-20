CREATE DATABASE IF NOT EXISTS
sistemajn2;
USE sistemajn2;

CREATE TABLE IF NOT EXISTS clientes (
  id INT(11) AUTO_INCREMENT,
  nome VARCHAR(255),
  telefone VARCHAR(255),
  CPF VARCHAR(255),
  placa_veiculo VARCHAR(255),
  PRIMARY KEY (id)
);
