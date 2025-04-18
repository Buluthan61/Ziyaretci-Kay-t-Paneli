<?php
$index = $_POST['index'] ?? null;
if ($index === null) exit;

$isim = $_POST['isim'];
$giris = $_POST['giris'];
$cikis = $_POST['cikis'] ?: null;

$dosya = 'kayitlar.json';
$veriler = json_decode(file_get_contents($dosya), true);

$veriler[$index]['isim'] = $isim;
$veriler[$index]['giris'] = $giris;
$veriler[$index]['cikis'] = $cikis;

file_put_contents($dosya, json_encode($veriler, JSON_PRETTY_PRINT));
header("Location: index.php");
