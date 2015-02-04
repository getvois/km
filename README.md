Kunstmaan Bundles Standard Edition
==================================

Welcome to the Kunstmaan Bundles Standard Edition - a fully-functional CMS (content management system) based on Symfony2 that you can use as the skeleton for your websites. Please refer to the documentation at [http://bundles.kunstmaan.be/getting-started](http://bundles.kunstmaan.be/getting-started) to get your CMS up and running.

Changes to kunstmaan bundles.
--------------------------------

### Russian letters to slug fix.

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/UtilitiesBundle/Helper/Slugifier.php

    /**
     * Convert russian to translit
     * @param $string
     * @return string
     */
    static function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',

            'ä' => 'a',   'á' => 'a',   'à' => 'a',
            'å' => 'a',   'é' => 'e',   'è' => 'e',
            'ë' => 'e',   'í' => 'i',   'ì' => 'i',
            'ï' => 'i',   'ó' => 'o',   'ò' => 'o',
            'ö' => 'o',   'ú' => 'u',   'ù' => 'u',
            'ü' => 'u',   'ñ' => 'n',   'ß' => 'ss',
            'æ' => 'ae',  'õ' => 'o',   'š' => 's',
            'ž' => 'z',

            'Ü' => 'U',   'Š' => 'S',   'Ä' => 'A',
            'Õ' => 'O',   'Ž' => 'z',

            '–' => '-',
        );
        return strtr(strtolower($string), $converter);
    }
    
    
        public static function slugify($text, $default = 'n-a', $replace = array("'"), $delimiter = '-')
        {
        
            .....
        
            // transliterate
            if (function_exists('iconv')) {
                $previouslocale = setlocale(LC_CTYPE, 0);
                
                /* Add this line */
                
                $text = self::rus2translit($text);
                
                /* End */
                
                setlocale(LC_CTYPE, 'en_US.UTF8');
                $text = iconv('utf-8', 'ISO-8859-1//IGNORE//TRANSLIT', $text);
                setlocale(LC_CTYPE, $previouslocale);
            }
    
            .....
        }

### Option resolver bug fix

###### File: /../vendor/symfony/symfony/src/Symfony/Component/OptionsResolver/OptionsResolver.php

    private function validateOptionsExistence(array $options)
    {
        $diff = array_diff_key($options, $this->knownOptions);

        if (count($diff) > 0) {
            ksort($this->knownOptions);
            ksort($diff);

    //            throw new InvalidOptionsException(sprintf(
    //                (count($diff) > 1 ? 'The options "%s" do not exist.' : 'The option "%s" does not exist.').' Known options are: "%s"',
    //                implode('", "', array_keys($diff)),
    //                implode('", "', array_keys($this->knownOptions))
    //            ));
        }
    }
    

### Host locale

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/NodeBundle/Controller/SlugController.php

    public function slugAction(Request $request, $url = null, $preview = false)
        {
            /* @var EntityManager $em */
            $em     = $this->getDoctrine()->getManager();
            $locale = $request->getLocale();
    
            //////CUSTOM CODE
            $host = $em->getRepository('SandboxWebsiteBundle:Host')
              ->findOneBy(['name' => $request->getHost()]);
    
            if($host && !$host->getMultiLanguage()){
                if($host->getLang() != $locale)
                    return new RedirectResponse($this->generateUrl('_slug', ['_locale' => $host->getLang(), 'url' => $url]));
            }
            ///////////
