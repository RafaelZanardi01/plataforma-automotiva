<?php
echo "<h1>Gerador de Hash de Senhas</h1>";
echo "<p>Use as senhas a seguir para inserir no banco de dados.</p>";
echo "<hr>";

// Senha para a Borracharia
$senha_borracharia = "borracha123"; // Defina uma senha forte!
$hash_borracharia = password_hash($senha_borracharia, PASSWORD_DEFAULT);
echo "<p><strong>Usuário Borracharia:</strong></p>";
echo "<p>Senha Simples: <code>" . htmlspecialchars($senha_borracharia) . "</code></p>";
echo "<p>Hash Gerado: <code>" . htmlspecialchars($hash_borracharia) . "</code></p>";
echo "<hr>";

// Senha para a Autopeças
$senha_autopecas = "pecas456"; // Defina uma senha forte!
$hash_autopecas = password_hash($senha_autopecas, PASSWORD_DEFAULT);
echo "<p><strong>Usuário Autopeças:</strong></p>";
echo "<p>Senha Simples: <code>" . htmlspecialchars($senha_autopecas) . "</code></p>";
echo "<p>Hash Gerado: <code>" . htmlspecialchars($hash_autopecas) . "</code></p>";
echo "<hr>";

// Senha para a Mecânica
$senha_mecanica = "mecanica789"; // Defina uma senha forte!
$hash_mecanica = password_hash($senha_mecanica, PASSWORD_DEFAULT);
echo "<p><strong>Usuário Mecânica:</strong></p>";
echo "<p>Senha Simples: <code>" . htmlspecialchars($senha_mecanica) . "</code></p>";
echo "<p>Hash Gerado: <code>" . htmlspecialchars($hash_mecanica) . "</code></p>";
echo "<hr>";

// Opcional: Senha para um Admin geral (se você criar um usuário admin)
$senha_admin = "adminmaster"; // Defina uma senha muito forte!
$hash_admin = password_hash($senha_admin, PASSWORD_DEFAULT);
echo "<p><strong>Usuário Administrador:</strong></p>";
echo "<p>Senha Simples: <code>" . htmlspecialchars($senha_admin) . "</code></p>";
echo "<p>Hash Gerado: <code>" . htmlspecialchars($hash_admin) . "</code></p>";
echo "<hr>";
?>