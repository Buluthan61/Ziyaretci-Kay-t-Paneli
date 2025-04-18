<?php
$tarih = $_GET['tarih'] ?? date('Y-m-d');
$dosya = 'kayitlar.json';

if (!file_exists($dosya)) {
    die("Kayıt dosyası bulunamadı.");
}

$veriler = json_decode(file_get_contents($dosya), true);
$bugun_kayitlar = array_filter($veriler, fn($k) => $k['tarih'] === $tarih);

$duzenliTarih = date('d-m-Y', strtotime($tarih));

// Excel için gerekli başlıklar
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ziyaretci_listesi_{$duzenliTarih}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

// Tablo başlığı
echo "<table border='1'>";
echo "<tr>
        <th>Ziyaretçi İsim Soyisim</th>
        <th>Giriş Zamanı</th>
        <th>Çıkış Zamanı</th>
        <th>Geçirilen Toplam Süre</th>
      </tr>";

foreach ($bugun_kayitlar as $k) {
    $isim = htmlspecialchars($k['isim']);
    $giris = date('d-m-Y H:i:s', strtotime($k['giris']));
    $cikis = isset($k['cikis']) ? date('d-m-Y H:i:s', strtotime($k['cikis'])) : '-';
    
    // Süre hesaplama kısmı
    if (!empty($k['giris']) && !empty($k['cikis'])) {
        $girisZaman = strtotime($k['giris']);
        $cikisZaman = strtotime($k['cikis']);
        $fark = $cikisZaman - $girisZaman;
        $saat = floor($fark / 3600);
        $dakika = floor(($fark % 3600) / 60);
        $saniye = $fark % 60;
        $sure = "$saat sa. $dakika dk. $saniye sn.";
    } else {
        $sure = "-";
    }

    echo "<tr>
            <td>{$isim}</td>
            <td>{$giris}</td>
            <td>{$cikis}</td>
            <td>{$sure}</td>
          </tr>";
}
echo "</table>";
?>
