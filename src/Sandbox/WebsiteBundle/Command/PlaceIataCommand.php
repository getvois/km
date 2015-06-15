<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlaceIataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:place:iata')
            ->setDescription('import places iata codes from api');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = file_get_contents('http://api.travelwebpartner.com/api/city.getAll');
        if(!$content) return;

        $data = json_decode($content);

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $i = 0;
        $total = count($data);

        $progress = new ProgressBar($output, $total);

        foreach ($data as $itm) {
            $id = $itm->id;
            $code = $itm->cityCode;

            $qb = $em->createQueryBuilder();
            $qb
                ->update('SandboxWebsiteBundle:Place\PlaceOverviewPage', 'p')
                ->set('p.iata', $qb->expr()->literal($code))
                ->where('p.cityId = ' . $id)
                ->getQuery()
                ->execute();

            $progress->advance();
        }

        $progress->finish();
    }
}