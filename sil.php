<?php
$index = $_POST['index'] ?? null;
if ($index === null) exit;

$dosya = 'kayitlar.json';
$veriler = json_decode(file_get_contents($dosya), true);

unset($veriler[$index]);
$veriler = array_values($veriler);

file_put_contents($dosya, json_encode($veriler, JSON_PRETTY_PRINT));
header("Location: index.php");
