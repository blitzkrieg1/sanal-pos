<?php

/**
 * Verilen vade farklarıyla taksit oranını hesaplamak
 * ve taksit tablosu çıktısı yapmak için interface.
 */
class TaksitHesap {
    protected $vadeFarklari;

    public function __construct($vadeFarklari) 
    {
        $this->vadeFarklari = $vadeFarklari;
    }

    /**
     * Belirli bir şablonda taksit tablosu hazırlayan method
     *
     * @param float $fiyat
     * @return string $htmlTaksitTablosu
     */
    public function taksitTablosu($fiyat)
    {
        $cikti  = '';
        $cikti .= '<table class="taksitTablosu">';
        for ($i=1; $i>0; $i++) {
            // Vade farkları burada son buluyorsa
            // Döngüyü durdur
            if ( ! isset($this->vadeFarklari[$i]))
                break;

            // Taksit hesapla
            $taksitFiyat = $this->taksitHesapla($fiyat, $i);

            $cikti .= "<tr><td>{$i}</td><td>{$taksitFiyat}</td></tr>";
        }
        $cikti .= '</table>';

        return $cikti;
    }

    /**
     * Verilen taksit
     *
     * @param float $fiyat
     * @param integer $taksitSayisi
     * @return float
     */
    public function taksitHesapla($fiyat, $taksitSayisi)
    {
        if ( ! isset($this->vadeFarklari[$taksitSayisi]))
            return false;

        return number_format(($this->vadeFarklari[$taksitSayisi] + 100) * $fiyat / 100, 2, '.', '');
    }
}