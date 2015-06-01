<?php

namespace Sandbox\WebsiteBundle\Helper;


class CurrencyConverter {

    private static $cache = [];

    /**
     * @param $fromCurrency string 3 char code of currency that needs to convert to EUR
     * @param $amount
     * @return float
     */
    public static function getPrice($fromCurrency, $amount)
    {
        return self::getCurrencyRate($fromCurrency) * $amount;
    }

    /**
     * @param $currency string 3 char code of currency that needs to convert to EUR
     * @return float
     */
    public static function getCurrencyRate($currency)
    {
        if(array_key_exists($currency, self::$cache)){
            return self::$cache[$currency];
        }

        if($currency == "EUR")
            return 1;
        do{
            $rate = null;
            $content = @file_get_contents('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20%28%22'.$currency.'EUR%22%29&format=json&env=store://datatables.org/alltableswithkeys&callback=');
            if($content){
                $res = json_decode($content);
                if(property_exists($res, 'query') &&
                    property_exists($res->query, 'results') &&
                    property_exists($res->query->results, 'rate') &&
                    property_exists($res->query->results->rate, 'Rate'))
                    $rate = $res->query->results->rate->Rate;
            }else{
                printf("Getting currency from appspot\n");
                $content = @file_get_contents('http://rate-exchange.appspot.com/currency?from='.$currency.'&to=EUR');
                if($content) {
                    $res = json_decode($content);
                    $rate = $res->rate;
                }
            }

        }while($content == false || $rate == null);

        self::$cache[$currency] = $rate;

        return $rate;
    }
}