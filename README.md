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
            'ä' => 'a'
        );
        return strtr($string, $converter);
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
                $text = iconv('utf-8', 'us-ascii//IGNORE//TRANSLIT', $text);
                setlocale(LC_CTYPE, $previouslocale);
            }
    
            .....
        }
        
        
        
### Admin places multi select in news and articles as Jquery [select2](http://ivaynberg.github.io/select2/)

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/AdminBundle/Resources/views/Default/layout.html.twig

    <!--=========== CSS ===========-->
    
    //ADD THIS LINE
    
    <link rel="stylesheet" href="{{ asset("bundles/sandboxwebsite/frontend/js/select2/select2.css") }}"/>
    <link rel="stylesheet" href="{{ asset("bundles/sandboxwebsite/css/style.css") }}"/>
    
    //END
    
    <!--Combine-->
    {% stylesheets
        "@KunstmaanAdminBundle/Resources/public/scss/style.scss"
        "@KunstmaanAdminBundle/Resources/public/js/chosen/chosen.css"
        filter="scss"
    %}


    .................
    .........

    <!--Extra-->
    {% block extrajavascript %}{% endblock %}

    //ADD THIS LINE.

    <script src="{{ asset("bundles/sandboxwebsite/frontend/js/select2/select2.min.js") }}"></script>

    //END

    <!--CKEDITOR-->
    <script>
    {% include "KunstmaanAdminBundle:Default:ckeditor.js.twig" %}
        jQuery(document).ready(function(){
            jQuery('textarea.rich_editor').each(function(item){
               ......
            });


            //ADD THIS LINES

            jQuery("#form_main_fromPlaces").select2();
            jQuery("#form_main_places").select2();
            jQuery("#form_main_place").select2();
            jQuery("#form_main_companies").select2();
            
            //END
            
        });

        {% include "KunstmaanAdminBundle:Default:_js_footer.html.twig" %}


     </script>
     
     
     
### Change Swedish country code from **sv** to **se** and add Estonia language

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/SearchBundle/Resources/config/analyzer_languages.yml

     //ADD
     ++ ee: { analyzer: 'estonian' }
     ++ se: { analyzer: 'swedish' }
     
     
### Tagging bundle registry change

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/TaggingBundle/Controller/TagAdminListController.php

    Change all routes to lowercase:

    kunstmaantaggingbundle_admin_tag
    kunstmaantaggingbundle_admin_tag_add
    kunstmaantaggingbundle_admin_tag_edit
    kunstmaantaggingbundle_admin_tag_delete
    kunstmaantaggingbundle_admin_tag_autocomplete


###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/TaggingBundle/Helper/Menu/TagMenuAdaptor.php

    Change route to lowercase:
    From:
    $menuitem->setRoute('KunstmaanTaggingBundle_admin_tag');
    To:
    $menuitem->setRoute('kunstmaantaggingbundle_admin_tag');
    
### Tagging bundle default options fix

###### File: /../vendor/kunstmaan/bundles-cms/src/Kunstmaan/TaggingBundle/Form/TagsAdminType.php

    //ADD
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions([]));
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
    

### Tag save

###### File: /../vendor/fpn/doctrine-extensions-taggable/lib/DoctrineExtensions/Taggable/TagManager.php

    public function saveTagging(Taggable $resource)
    {

        $oldTags = $this->getTagging($resource);
        
        //ADD clone
        $newTags = clone $resource->getTags();
        
        $tagsToAdd = $newTags;