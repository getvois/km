<?php
namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\SeoBundle\Entity\Seo;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Input\ArrayInput;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Input\InputInterface;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Output\OutputInterface;

class OffersGrouponUkCommand extends OffersGrouponCommand
{
    protected $lang = 'en';
    protected $currncy = 'GBP';

    protected function configure()
    {
        $this
            ->setName('travelbase:import:offersgroupon:uk')
            ->setDescription('Import groupon offers')
        ;
    }


    /**
     * @return array
     */
    protected function getOffers()
    {
        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $content = @file_get_contents('https://partner-int-api.groupon.com/deals.json?tsToken=IE_AFF_0_201236_212556_0&country_code=UK&filters=topcategory:travel', false, $context);

        if(!$content) return [];

        $data = json_decode($content);

        return $data->deals;
    }
}