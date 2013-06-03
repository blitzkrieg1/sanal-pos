<?php

class TaksitHesapTest extends PHPUnit_Framework_TestCase {
    protected $taksitHesap;

    public function setUp()
    {
        $this->taksitHesap = new TaksitHesap([
                1 => 0,
                2 => 5,
                3 => 0,
                4 => 10
            ]);
    }

    public function testTaksitDegeriniHesapla()
    {
        $this->assertEquals(10, $this->taksitHesap->taksitHesapla(10, 1));
        $this->assertEquals(10.5, $this->taksitHesap->taksitHesapla(10, 2));
        $this->assertEquals(10, $this->taksitHesap->taksitHesapla(10, 3));
        $this->assertEquals(11, $this->taksitHesap->taksitHesapla(10, 4));
    }

    public function testHtmlTest()
    {
        $beklenenHTML = '<table class="taksitTablosu"><tr><td>1</td><td>10.00</td></tr><tr><td>2</td><td>10.50</td></tr><tr><td>3</td><td>10.00</td></tr><tr><td>4</td><td>11.00</td></tr></table>';
        $this->assertEquals($this->taksitHesap->taksitTablosu(10), $beklenenHTML);
    }

    public function testGecersizTaksit()
    {
        $this->assertEquals($this->taksitHesap->taksitHesapla(10, 5), false);
    }
}