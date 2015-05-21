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

        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages('en');

        $categories = [];

        foreach ($offers as $offer) {
            $categories[] = $offer->getCategory();
        }

        $categories = array_unique($categories);

        foreach ($categories as $c) {
            echo $c . "\n";
        }


    }
}