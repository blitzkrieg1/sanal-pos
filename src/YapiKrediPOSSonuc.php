<?php

/**
 * POSSonuc interfacei ile Yapı Kredi POS döngüleri 
 */
class YapiKrediPOSSonuc implements POSSonuc
{
    public $rawDongu;

    public function __construct($rawDongu)
    {
        $this->rawDongu = $rawDongu;
    }

    public function basariliMi()
    {

    }

    public function hataMesajlari()
    {
        
    }

    public function raw()
    {
        return $this->rawDongu;
    }
}