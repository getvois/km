<?php
namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class HotelliveebCheapestPackageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:bind:hotelliveeb:cheapestpackage')
            ->setDescription('Set hotels cheapest packages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $langs = ['ee', 'en', 'fi', 'ru'];

        foreach ($langs as $lang) {
            $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
                ->getHotelPages($lang);

            $this->bindCheapestPackages($hotels, $lang);
        }





    }

    private function bindCheapestPackages($hotels, $lang)
    {
        if(!$hotels) return;

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var HotelPage $hotel */
        foreach ($hotels as $hotel) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($hotel);

            $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
                ->getPackagesByParent($lang, $node);

            if(!$packages) continue;

            $cheapest = null;
            $cheapestPrice = 99999;
            foreach ($packages as $package) {
                if($cheapestPrice > $package->getMinprice()){
                    $cheapestPrice = $package->getMinprice();
                    $cheapest = $package;
                }
            }

            $hotel->setCheapestPackage($cheapest);
            $em->persist($hotel);
            $em->flush();
        }

    }
}