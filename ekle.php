<?php
date_default_timezone_set('Europe/Istanbul');

$isim = $_POST['isim'] ?? '';
if (!$isim) exit;

$dosya = 'kayitlar.json';
$veriler = [];

if (file_exists($dosya)) {
    $veriler = json_decode(file_get_contents($dosya), true);
}

$veriler[] = [
    'isim' => $isim,
    'giris' => date('Y-m-d H:i:s'),
    'cikis' => null,
    'tarih' => date('Y-m-d')
];

file_put_contents($dosya, json_encode($veriler, JSON_PRETTY_PRINT));
header("Location: index.php");
