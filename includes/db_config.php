<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost'); // Geralmente 'localhost' para ambiente local
define('DB_USER', 'root');     // Usuário padrão do MySQL no XAMPP é 'root'
define('DB_PASS', '123456'); // <--- IMPORTANTE: COLOQUE A SENHA QUE VOCÊ DEFINIU!
define('DB_NAME', 'plataforma_automotiva'); // Nome do seu banco de dados no Workbench/phpMyAdmin

// Você pode adicionar um teste básico de conexão aqui (remover depois!)
// $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// if ($conn->connect_error) {
//     die("Erro de conexão com o banco de dados: " . $conn->connect_error);
// }
// echo "Conexão com o banco de dados bem-sucedida!";
// $conn->close();
?>