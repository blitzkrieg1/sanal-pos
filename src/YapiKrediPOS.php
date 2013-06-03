<?php

/**
 * Yapı Kredi için sanal POS
 */
class YapiKrediPOS implements POSInterface
{
    protected $posnet;

    /**
     * Banka ayarları
     */
    protected $host;
    protected $musteriID;
    protected $terminalID;

    public function __construct(Posnet $posnet, $musteriID, $terminalID)
    {
        // Posnet injection
        $this->posnet = $posnet;

        // Banka giriş verileri
        $this->musteriID  = $musteriID;
        $this->terminalID = $terminalID;
    }

    public function dogrula()
    {
        
    }

    public function odeme()
    {
        
    }
}