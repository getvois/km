<?php

namespace Sandbox\WebsiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeParentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:kuma:change-node-parent')
            ->setDescription('Change the parent of a Node')
            ->addArgument(
                'child',
                InputArgument::REQUIRED,
                'ID of child not that will be moved'
            )
            ->addArgument(
                'parent',
                InputArgument::REQUIRED,
                'ID of parent Node to move child to'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em=$this->getContainer()->get('doctrine')->getManager();

        $child = $em->getRepository('KunstmaanNodeBundle:Node')
            ->find($input->getArgument('child'));
        if(!$child) {
            $output->writeln('child does not exist.');
            return;
        }

        $parent = $em->getRepository('KunstmaanNodeBundle:Node')
            ->find($input->getArgument('parent'));
        if(!$parent){
            $output->writeln('parent does not exist.');
            return;
        }

        $child->setParent($parent);

        $em->persist($child);
        $em->flush();
        $output->writeln('Done.');
    }
}