<?php namespace SanalPos\Est;

use SanalPos\BasePos;

/**
 * EST için sanal POS
 */
class Pos extends BasePos implements \SanalPos\PosInterface {
    protected $est;

    /**
     * Kart bilgileri
     */
    protected $kartNo;
    protected $sonKullanmaTarihi;
    protected $cvc;

    /** 
     * Sipariş bilgileri
     */
    protected $tutar;
    protected $siparisID;
    protected $taksit;

    /**
     * Est nesnesinin injectionı, sanal pos bilgileri ve environment
     * belirlemek için kullanılıyor.
     *
     * @param Est $est
     * @param string $isyeriID
     * @param string $kullanici
     * @param string $parola
     * @param string $environment
     * @return void
     */
    public function __construct(\Est $est)
    {
        // Est injection
        $this->est = $est;
    }

    /**
     * Kredi kartı ayarlarını yap
     *
     * @param string $kartNo
     * @param string $sonKullanmaTarihi
     * @param string $cvc
     * @return void
     */
    public function krediKartiAyarlari($kartNo, $sonKullanmaTarihi, $cvc)
    {
        $this->kartNo            = $kartNo;
        $this->sonKullanmaTarihi = $sonKullanmaTarihi;
        $this->cvc               = $cvc;
    }

    /**
     * Sipariş ayarlarını belirle
     *
     * @param float $tutar
     * @param string $siparisID
     * @return void
     */
    public function siparisAyarlari($tutar, $siparisID, $taksit)
    {
        $this->tutar     = $tutar;
        $this->siparisID = $siparisID;
        $this->taksit    = $taksit;
    }

    /**
     * Ayarları yapılan ödemeyi gerçekleştir
     *
     * @return PosSonucInterface
     */
    public function odeme()
    {
        // Kontrol yapmadan deneme yapan olabilir
        if ( ! $this->dogrula())
            throw new \InvalidArgumentException;

        // Verileri EST'ye uyumlu hale getir
        $sktAy  = substr($this->sonKullanmaTarihi, 0, 2);
        $sktYil = substr($this->sonKullanmaTarihi, 2, 2);
        $tutar  = number_format($this->tutar, 2, '.', '');

        $sonuc = $this->est->pay($this->kartNo, $this->cvc, $sktAy, $sktYil, $tutar, $this->taksit, $this->siparisID);

        // Sonuç nesnesini oluştur
        return new Sonuc($sonuc);
    }

}