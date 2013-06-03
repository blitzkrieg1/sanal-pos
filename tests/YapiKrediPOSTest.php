<?php

/**
 * Yapı Kredi POS testleri 
 */
class YapiKrediPOSTest extends PHPUnit_Framework_TestCase {
    protected $pos;

    public function setUp() 
    {
        // POS Net mock
        $posnet = \Mockery::mock('Posnet');

        $this->pos = new YapiKrediPOS($posnet, 'MUSTERIID', 'TERMINALID', 'test');
    }

     public function tearDown()
    {
        \Mockery::close();
    }

    public function testGecersizKrediKarti() 
    {
        $this->pos->krediKartiAyarlari('GECERSIZKREDIKARTI', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecersizSonKullanmaTarihiFormati() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '10211', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testSonKullanmaTarihiGecersizAy() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1314', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecmisSonKullanmaTarihi() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1012', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecersizCCV() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '1234');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testSifirHarcama() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(0.00, 'SIPARISID');

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecerliSiparisDogrulama() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        $this->assertTrue($this->pos->dogrula());
    }

    /** 
     * @expectedException InvalidArgumentException
     */
    public function testDogrulamadanOdemeDenemesi() 
    {
        $this->pos->odeme();
    }

    public function testAuthOdeme() 
    {
        // Özel mock
        $posnet = \Mockery::mock('Posnet');
        $posnet->shouldReceive('UseOpenssl')->once()->andReturn('1');
        $posnet->shouldReceive('SetURL')->once()->andReturn('1');
        $posnet->shouldReceive('SetMid')->once()->andReturn('1');
        $posnet->shouldReceive('SetTid')->once()->andReturn('1');
        $posnet->shouldReceive('DoAuthTran')->once()->andReturn('1');

        $this->pos = new YapiKrediPOS($posnet, 'MUSTERIID', 'TERMINALID', 'test');

        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID');

        // Döngü türü kontrolü
        $this->assertInstanceOf('YapiKrediPOSSonuc', $this->pos->odeme());
        // Döngü mesajı kontrolü
        // $this->assertEquals('', $this->pos->);
    }

    public function testBaglantiAyarlariDegistirme()
    {
        $yeniAyarlar = array('timeOut' => 10, 'ip' => 'xx.xx.xx.xx');
        $this->pos->baglantiAyarlari($yeniAyarlar);
        $this->assertEquals($yeniAyarlar, $this->pos->baglantiAyarlari);
    }
}