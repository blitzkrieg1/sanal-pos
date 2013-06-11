<?php

require 'vendor/autoload.php';

// EstPos nesnesini oluştur
$pos = new \SanalPos\YapiKredi\Pos(new \Posnet, '6784621218', '67104034');
$pos->krediKartiAyarlari('4543600290478695', '1114', '000');
$pos->siparisAyarlari(00.01, 'SIPARISID' . time(), 0);
$sonuc = $pos->odeme();

var_dump($sonuc);
var_dump($sonuc->basariliMi());
var_dump($sonuc->hataMesajlari());