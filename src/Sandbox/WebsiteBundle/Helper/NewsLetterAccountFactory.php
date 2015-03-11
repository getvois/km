<?php

namespace Sandbox\WebsiteBundle\Helper;


class NewsLetterAccountFactory {

    /** @var NewsLetterEmailAccount[]  */
    private $accounts = [];

    function __construct()
    {
        $account = new NewsLetterEmailAccount('mika.mendesee@gmail.com', 'qwerty121284', 'ee');
        $patterns = [
            '/Kui soovi(d|te) uudiskirja/',
            '/Kui (Sa|Te) ei (soovi|näe)/',
            '/uudiskirjast loobuda/',
            '/ei näe (pilte|uudiskirja)/',
            '/This email was sent to/',
            '/Eemalda e-mail nimekirjast/',
            '/software by/',
            '/Mailbow/',
            '/gmail.com/',
            '/Hei Mika/',
            '/Kui emaili ei kuvata/',
            '/Uudiskirjast lahkumiseks/',
            '/uudiskiri ei avane/',
            //'/Ei soovi rohkem kirju saada?/',
        ];
        $account->setFilterPatterns($patterns);
        $this->accounts[] = $account;

        $account = new NewsLetterEmailAccount('mika.mendesfi@gmail.com', 'qwerty121285', 'fi');
        $patterns = [
            '/Jos luet viestin/',
            '/Rekisteriseloste/',
            '/lähetetty sähköpostiosoitteeseen/',
            '/et jatkossa halua/',
            '/Avaa uutiskirje selaimeen/',
            '/Päivitä (yhteys|tietosi)/',
            '/Lähetetty osoitteeseen/',
            '/et enää halua/',
            '/haluat tehdä muutoksia/',
            '/Osoitelähde/',
            '/Ei kuvia/',
            '/ei näy oikein/',
            '/Älä vastaa tähän viestiin/',
            '/Omat tiedot/',
            '/Miksi minulle tulee/',
            '/tehdä muutoksia/',
            '/Katso selaimessa/',
            '/uutiskirje on lähetetty/',
            '/Hei mika/',
            '/Osoiterekisteri/',
            '/Jos et pysty/',
            '/Peruuta tilaus/',
            '/Omat/',
            '/Jos et halua/',
            '/Michael/',
        ];
        $account->setFilterPatterns($patterns);
        $this->accounts[] = $account;
        $account = new NewsLetterEmailAccount('mika.mendesse@gmail.com', 'qwerty121284', 'se');
        $patterns = [];
        $account->setFilterPatterns($patterns);
        $this->accounts[] = $account;
        $account = new NewsLetterEmailAccount('mika.mendesen@gmail.com', 'qwerty121284', 'en');
        $patterns = [];
        $account->setFilterPatterns($patterns);
        $this->accounts[] = $account;
    }

    /**
     * @return NewsLetterEmailAccount[]
     */
    public function getAll()
    {
        return $this->accounts;
    }

    /**
     * @param $locale string|array of locales
     * @return NewsLetterEmailAccount[]
     */
    public function getByLocale($locale)
    {
        $res = [];
        foreach ($this->accounts as $acc) {
            if(is_array($locale)){
                if(in_array($acc->getLocale(), $locale)){
                    $res[] = $acc;
                }
            }else{
                if($acc->getLocale() == $locale){
                    $res[] = $acc;
                }
            }
        }

        return $res;
    }

}