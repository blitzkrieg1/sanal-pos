<?php

/**
 * POSSonuc interfacei ile Yapı Kredi POS döngüleri 
 */
class EstPOSSonuc implements POSSonuc {
    public $estDongu;

    /**
     * Est'den gelen sonuç dizisini kaydet
     *
     * @param string $estDongu
     * @return void
     */
    public function __construct($estDongu)
    {
        $this->estDongu = $estDongu;
    }

    public function basariliMi()
    {
        return intval($this->estDongu['return_code']) == 0;
    }

    public function hataMesajlari()
    {
        return array(
                array(
                    'kod'   => $this->estDongu['return_code'],
                    'mesaj' => $this->estDongu['error_msg']
                )
            );
    }

    public function raw()
    {
        return $this->estDongu;
    }
}