<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:test')
            ->setDescription('test');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages('ee');

        foreach ($hotels as $hotel) {
            if($hotel->getCountryPlace() && $hotel->getCountryPlace()->getTitle() == 'Tallinn')
                var_dump($hotel->getTitle() . " " . $hotel->getCountryPlace()->getTitle());
        }

    }
}