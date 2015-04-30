<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HotelliveebTranslateCommand extends ContainerAwareCommand{

    private $key = 'AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM';

    protected function configure()
    {
        $this
            ->setName('travelbase:translate:hotelliveeb')
            ->setDescription('Translate hotelliveeb hotel info title to fin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->translateFi();
        //$this->translateRu();
    }

    private function translateFi()
    {
       $this->translateTo('fi');
    }
    private function translateRu()
    {
       $this->translateTo('ru');
    }


    private function translateTo($lang)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages($lang);

        if(!$hotels) $hotels = [];

        foreach ($hotels as $hotel) {
            $pageparts = $em
                ->getRepository('KunstmaanPagePartBundle:PagePartRef')
                ->getPageParts($hotel, 'information');

            if(!$pageparts) $pageparts = [];

            foreach ($pageparts as $pagepart) {
                if($pagepart instanceof HotelInformationPagePart){
                    //if has name and is not translated
                    if($pagepart->getName() && !$pagepart->getNameTranslated()){
                        usleep(1000);
                        $name = $pagepart->getName();
                        $url = 'https://www.googleapis.com/language/translate/v2?key='.$this->key.'&source=et&target='.$lang.'&q='.urlencode($name);
                        $content = @file_get_contents($url);
                        if($content){
                            $data = json_decode($content);
                            if(array_key_exists('data', $data)
                                && array_key_exists('translations', $data->data)
                                && array_key_exists('0', $data->data->translations)
                                && array_key_exists('translatedText', $data->data->translations[0])){
                                $translatedName = $data->data->translations[0]->translatedText;
                                $pagepart->setNameTranslated($translatedName);
                                $em->persist($pagepart);

                                var_dump('(EE)' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName);
                            }else{
                                var_dump($data);
                            }
                        }else{
                            var_dump('Error loading: ' . $url);
                        }
                    }
                }

            }
            $em->flush();
        }
    }
}