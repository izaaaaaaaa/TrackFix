<?php
// htdocs/config/database.php

$host = 'sql308.infinityfree.com'; // Encontre no seu painel (MySQL Host)
$db   = 'if0_41199095_db_trackfix';       // Nome do seu banco de dados
$user = 'if0_41199095';            // Seu usuário do banco
$pass = 'm3108leal';          // Sua senha do painel

try {
    // A variável PRECISA se chamar $pdo para o index.php reconhecê-la
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
   
    // Configura o PDO para avisar sobre erros em vez de falhar silenciosamente
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Se a senha ou host estiverem errados, ele mostrará o motivo real aqui
    die("Falha na conexão técnica: " . $e->getMessage());
}
