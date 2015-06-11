<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\UtilitiesBundle\Helper\Slugifier;
use Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HotelliveebTranslateTitleCommand extends ContainerAwareCommand{

    private $key = 'AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM';
    private $emailBody = '';

    protected function configure()
    {
        $this
            ->setName('travelbase:translate:packages:title')
            ->setDescription('Translate packages title to fin and ru')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->translateFi();
        $this->translateRu();

        if($this->emailBody){
            $email = 'HV Package titles translated<br/>' . $this->emailBody;
            EmailInfoSend::sendEmail($email, 'HV Package titles translated');
        }
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
            $slugifier = new Slugifier();
            if(!$package->getTitleTranslated()){
                usleep(1000);
                $name = $package->getTitle();
                $url = 'https://www.googleapis.com/language/translate/v2?key='.$this->key.'&source=et&target='.$lang.'&q='.urlencode($name);
                $content = @file_get_contents($url);
                if($content){
                    $data = json_decode($content);
                    if(array_key_exists('data', $data)
                        && array_key_exists('translations', $data->data)
                        && array_key_exists('0', $data->data->translations)
                        && array_key_exists('translatedText', $data->data->translations[0])){
                        $translatedName = $data->data->translations[0]->translatedText;
                        $package->setTitleTranslated($translatedName);
                        $em->persist($package);

                        //set slug
                        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
                            ->getNodeTranslationFor($package);
                        if($translation){
                            $translation->setSlug($slugifier->slugify($translatedName));
                            $em->persist($translation);
                        }

                        $em->flush();
                        var_dump('(EE)' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName);
                        $this->emailBody .= 'Node: ' . $translation->getNode()->getId() . ' (EE)' . $name . ' <===> ('.strtoupper($lang).')' . $translatedName .'<br/>';
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