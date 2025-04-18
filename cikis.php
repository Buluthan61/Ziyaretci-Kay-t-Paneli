<?php
date_default_timezone_set('Europe/Istanbul');

$index = $_POST['index'] ?? null;
if ($index === null) exit;

$dosya = 'kayitlar.json';
$veriler = json_decode(file_get_contents($dosya), true);

if (isset($veriler[$index])) {
    $veriler[$index]['cikis'] = date('Y-m-d H:i:s');
    file_put_contents($dosya, json_encode($veriler, JSON_PRETTY_PRINT));
}

header("Location: index.php");
