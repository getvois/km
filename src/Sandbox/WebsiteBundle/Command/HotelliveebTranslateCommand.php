<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HotelliveebTranslateCommand extends ContainerAwareCommand{

    private $key = 'AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM';

    protected function configure()
    {
        $this
            ->setName('travelbase:translate:hotelliveeb:packages')
            ->setDescription('Translate hotelliveeb packages title to fin + slug')
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

        /** @var PackagePage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($lang);

        if(!$packages) $packages = [];

        foreach ($packages as $package) {
            $pageparts = $em
                ->getRepository('KunstmaanPagePartBundle:PagePartRef')
                ->getPageParts($package, 'information');

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