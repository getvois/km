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

class OffersGrouponDeCommand extends OffersGrouponCommand
{
    protected $lang = 'de';

    protected function configure()
    {
        $this
            ->setName('travelbase:import:offersgroupon:de')
            ->setDescription('Import groupon offers')
        ;
    }
}