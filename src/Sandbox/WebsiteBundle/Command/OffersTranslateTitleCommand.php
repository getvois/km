<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\UtilitiesBundle\Helper\Slugifier;
use Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OffersTranslateTitleCommand extends ContainerAwareCommand{

    private $key = 'AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM';

    protected function configure()
    {
        $this
            ->setName('travelbase:translate:offers:title')
            ->setDescription('Translate packages title to ee + slug')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$this->translateEe();
        $this->translateRu();
    }

    private function translateEe()
    {
       $this->translateTo('ee');
    }
    private function translateRu()
    {
       $this->translateTo('ru');
    }


    private function translateTo($lang)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var PackagePage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($lang);

        if(!$offers) $offers = [];

        if($lang == 'ee') $lang = 'et';

        foreach ($offers as $offer) {
            if(!$offer->getTitleTranslated()){
                usleep(1000);
                $name = $offer->getTitle();
                $url = 'https://www.googleapis.com/language/translate/v2?key='.$this->key.'&source=fi&target='.$lang.'&q='.urlencode($name);
                $content = @file_get_contents($url);
                if($content){
                    $data = json_decode($content);
                    if(array_key_exists('data', $data)
                        && array_key_exists('translations', $data->data)
                        && array_key_exists('0', $data->data->translations)
                        && array_key_exists('translatedText', $data->data->translations[0])){
                        $translatedName = $data->data->translations[0]->translatedText;
                        $offer->setTitleTranslated($translatedName);
                        $em->persist($offer);

                        //set slug
                        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
                            ->getNodeTranslationFor($offer);
                        if($translation){
                            $translation->setSlug(Slugifier::slugify($translatedName));
                            $em->persist($translation);
                        }

                        $em->flush();
                        var_dump('(FI)' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName);
                    }else{
                        var_dump($data);
                    }
                }else{
                    var_dump('Error loading: ' . $url);
                }
            }
        }

    }
}