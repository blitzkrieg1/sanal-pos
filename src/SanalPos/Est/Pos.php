<?php namespace SanalPos\Est;

/**
 * EST için sanal POS
 */
class Pos implements \SanalPos\PosInterface {
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
     * Tüm kontrolleri yap
     *
     * @return bool
     */
    public function dogrula()
    {
        return $this->krediKartiKontrolleri() and $this->siparisKontrolleri();
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

    /**
     * Kontrol methodları
     */
    /**
     * Kredi kartı için tüm kontrolleri yapan method
     *
     * @return bool
     */
    protected function krediKartiKontrolleri()
    {
        return $this->kartNoKontrolleri() and $this->sonKullanmaTarihiKontrolleri() and $this->cvcKontrolleri();
    }

    /**
     * Kredi kart numarasının geçerliliğini kontrol eden method
     *
     * @return bool
     */
    protected function kartNoKontrolleri()
    {
        return preg_match('/^[0-9]{16}$/', $this->kartNo);
    }

    /**
     * Kredi kart son kullanma tarihinin geçerliliğini kontrol eden method
     *
     * @return bool
     */
    protected function sonKullanmaTarihiKontrolleri()
    {
        // 4 karakter ve numerik
        if ( ! preg_match('/^[0-9]{4}$/', $this->sonKullanmaTarihi))
            return false;

        // Ay kontrolü
        // 1-12 arası olmak zorunda
        $ay = (int) substr($this->sonKullanmaTarihi, 0, 2);
        if ($ay > 12 or $ay <= 0)
            return false;

        // Yıl kontrolü
        // Geçmiş yıl olamaz
        $yil = (int) substr($this->sonKullanmaTarihi, 2, 2);
        if ($yil < date('y'))
            return false;

        return true;
    }

    /**
     * Kredi kartı güvenlik kodunun geçerliliğini kontrol eden method
     *
     * @return bool
     */
    public function cvcKontrolleri()
    {
        return preg_match('/^[0-9]{3}$/', $this->cvc);
    }

    /**
     * Sipriş için girilen tüm verileri kontrol eden method
     *
     * @return bool
     */
    protected function siparisKontrolleri()
    {
        return $this->tutarKontrolleri() and $this->siparisIDKontrolleri();
    }

    /**
     * Girilen sipariş tutarını kontrol eden method
     *
     * @return bool
     */
    protected function tutarKontrolleri()
    {
        return is_numeric($this->tutar) and $this->tutar > 0;
    }

    /**
     * Girilen sipariş ID'sini kontrol eden method
     *
     * @return bool
     */
    protected function siparisIDKontrolleri()
    {
        return ! empty($this->siparisID);
    }

}