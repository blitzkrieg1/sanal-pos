<?php namespace SanalPos\YapiKredi;

use SanalPos\BasePos;
use \Posnet;

/**
 * Yapı Kredi için sanal POS
 */
class Pos extends BasePos implements \SanalPos\PosInterface {
    protected $posnet;

    /**
     * Banka ayarları
     */
    protected $hostlar = array(
            'test'       => 'http://setmpos.ykb.com/PosnetWebService/XML',
            'production' => 'https://www.posnet.ykb.com/PosnetWebService/XML'
        );
    protected $host;
    protected $musteriID;
    protected $terminalID;

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
     * Bağlantı ayarları
     */
    public $baglantiAyarlari = array(
            'timeOut' => 30
        );

    /**
     * Posnet nesnesinin injectionı, sanal pos bilgileri ve environment
     * belirlemek için kullanılıyor.
     *
     * @param Posnet $posnet
     * @param string $musteriID
     * @param string $terminalID
     * @param string $environment
     * @return void
     */
    public function __construct(Posnet $posnet, $musteriID, $terminalID, $environment = 'production')
    {
        // Posnet injection
        $this->posnet = $posnet;

        // Banka giriş verileri
        $this->musteriID  = $musteriID;
        $this->terminalID = $terminalID;
        $this->host       = $this->hostlar[$environment];
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
     * Bağlantı ayarlarını düzenle
     *
     * @param array $yeniAyarlar
     * @return void
     */
    public function baglantiAyarlari($yeniAyarlar)
    {
        $this->baglantiAyarlari = array_merge($this->baglantiAyarlari, $yeniAyarlar);
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

        // Bankaya post edilecek veriler
        $kur = 'YT';

        // İşlem tutarını düzenle
        $tutar = number_format($this->tutar, 2, '', '');

        // Son kullanma tarihi formatı
        $sktAy  = substr($this->sonKullanmaTarihi, 0, 2);
        $sktYil = substr($this->sonKullanmaTarihi, 2, 2);

        $this->posnet->SetURL($this->host);
        $this->posnet->SetMid($this->musteriID);
        $this->posnet->SetTid($this->terminalID);
        $this->posnet->DoSaleTran(
            $this->kartNo,
            $sktYil . $sktAy,
            $this->cvc,
            $this->siparisID,
            $tutar,
            $kur,
            $this->taksit
        );

        // Sonuç nesnesini oluştur
        return new Sonuc($this->posnet);
    }

}