<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\UtilitiesBundle\Helper\Slugifier;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OffersTranslateTitleCommand extends ContainerAwareCommand{

    private $key = 'AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM';
    private $emailBody = '';

    protected function configure()
    {
        $this
            ->setName('travelbase:translate:offers:title')
            ->setDescription('Translate packages title to ee ru + slug')
            ->addArgument(
                'originalLang',
                InputArgument::REQUIRED,
                'Original language of offers e.g en,fi'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $originalLang = $input->getArgument('originalLang');
        if($originalLang == 'fi') {
            $this->translateEe('fi');
            $this->translateRu('fi');
            $this->translateEn('fi');
        }elseif($originalLang == 'en') {
            $this->translateEe('en');
            $this->translateRu('en');
            $this->translateFi('en');
        }

        if($this->emailBody){
            $email = 'Offers titles translated<br/>' . $this->emailBody;
            EmailInfoSend::sendEmail($email, 'Offers titles translated');
        }
    }

    private function translateEe($originalLang)
    {
       $this->translateTo('ee', $originalLang);
    }
    private function translateRu($originalLang)
    {
       $this->translateTo('ru', $originalLang);
    }
    private function translateEn($originalLang)
    {
       $this->translateTo('en', $originalLang);
    }
    private function translateFi($originalLang)
    {
       $this->translateTo('fi', $originalLang);
    }


    private function translateTo($lang, $originalLang)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($lang, $originalLang);

        if(!$offers) $offers = [];

        if($lang == 'ee') $lang = 'et';

        foreach ($offers as $offer) {
            //$this->translateShortDescription($offer, $lang, $originalLang);
            $this->translateTitle($offer, $lang, $originalLang);
        }

    }


    private function translateTitle(OfferPage $offer, $lang, $originalLang)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        if(!$offer->getTitleTranslated()){
            usleep(1000);
            $name = $offer->getTitle();
            $url = 'https://www.googleapis.com/language/translate/v2?key='.$this->key.'&source='.$originalLang.'&target='.$lang.'&q='.urlencode($name);
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
                    var_dump('('.strtoupper($originalLang).')' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName);
                    $this->emailBody .= 'Node: ' . $translation->getNode()->getId() . ' ('.strtoupper($originalLang).')' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName .'<br/>';
                }else{
                    var_dump($data);
                }
            }else{
                var_dump('Error loading: ' . $url);
            }
        }
    }

    private function translateShortDescription(OfferPage $offer, $lang, $originalLang)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        if(!$offer->getShortDescriptionTranslated()){
            usleep(1000);
            $name = $offer->getShortDescription();
            $url = 'https://www.googleapis.com/language/translate/v2?key='.$this->key.'&source='.$originalLang.'&target='.$lang.'&q='.urlencode($name);
            $content = @file_get_contents($url);
            if($content){
                $data = json_decode($content);
                if(array_key_exists('data', $data)
                    && array_key_exists('translations', $data->data)
                    && array_key_exists('0', $data->data->translations)
                    && array_key_exists('translatedText', $data->data->translations[0])){
                    $translatedName = $data->data->translations[0]->translatedText;
                    $offer->setShortDescriptionTranslated($translatedName);
                    $em->persist($offer);

                    $node = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($offer);

                    $em->flush();
                    var_dump('('.strtoupper($originalLang).')' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName);
                    $this->emailBody .= 'Node: ' . $node->getId() . ' ('.strtoupper($originalLang).')' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName .'<br/>';
                }else{
                    var_dump($data);
                }
            }else{
                var_dump('Error loading: ' . $url);
            }
        }
    }
}