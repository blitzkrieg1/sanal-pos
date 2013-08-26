<?php namespace SanalPos\Garanti;

use SimpleXMLElement;

/**
 * POSSonuc interfacei ile Yapı Kredi POS döngüleri 
 */
class Sonuc implements \SanalPos\PosSonucInterface {
    public $orijinalXml;
    public $xml;

    /**
     * Garanti'den gelen sonuç dizisini kaydet
     *
     * @param string $xml
     * @return void
     */
    public function __construct($xml)
    {
        $this->orijinalXml = $xml;
        $this->xml         = new SimpleXMLElement($xml);
    }

    public function basariliMi()
    {
        return (string) $this->xml->Transaction->Response->Code[0] === '00';
    }

    public function hataMesajlari()
    {
        return array(
                array(
                    'kod'   => '',
                    'mesaj' => ''
                )
            );
    }

    public function raw()
    {
        return $this->orijinalXml;
    }
}