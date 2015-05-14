<?php

namespace Sandbox\WebsiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMarkersCommand extends ContainerAwareCommand{
    protected function configure()
    {
        $this
            ->setName('travelbase:create:markers')
            ->setDescription('create markers for map')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->testGD();

        $icons = 500;
        $progress = new ProgressBar($output, $icons);
        $progress->start();
        for($i=0; $i<=$icons; $i++){
            if($i<50){
                $this->createMarker('marker_4.png', $i);
            }elseif($i < 70){
                $this->createMarker('marker_3.png', $i);
            }elseif($i < 100){
                $this->createMarker('marker_2.png', $i);
            }else{
                $this->createMarker('marker_1.png', $i);
            }

            $progress->setCurrent($i);
        }

        $progress->finish();

        $progress = new ProgressBar($output, $icons);
        $progress->start();
        for($i=0; $i<=$icons; $i++){
            $this->createMarker('marker_b2.png', $i, 'marker_b', 2);
            $progress->setCurrent($i);
        }

        $progress->finish();
    }

    private function createMarker($template, $number, $filename = 'marker', $offsetY = -3)
    {
        $text = $number;
        $fontsize = 12;
        $offsetX = 0;

        $path = $this->getContainer()->getParameter('kernel.root_dir') . "/../src/Sandbox/WebsiteBundle/Resources/public/img/markers/";
        $img = imagecreatefrompng($path . 'template/' . $template);
        list($width, $height) = getimagesize($path . 'template/' . $template);
        imagesavealpha($img, true);
        imagealphablending($img, false);
//        $color = imagecolorallocatealpha($img, 0, 0, 0, 127);
//        imagefill($img, 0, 0, $color);

        $fontWidth = imagefontwidth($fontsize) * strlen($text);
        $fontHeight = imagefontheight($fontsize);

        $posX = $width/2 - $fontWidth/2 + $offsetX;
        $posY = $height/2 - $fontHeight/2 + $offsetY;

        $textColor = imagecolorallocate($img, 235, 235, 245);
        imagestring($img, $fontsize, $posX, $posY, $text, $textColor);

        imagepng($img, $path.'generated/'.$filename.$number.'.png');
    }

    private function testGD()
    {
        $testGD = get_extension_funcs("gd"); // Grab function list
        if (!$testGD){ echo "GD not even installed.\nRun: sudo apt-get install php5-gd or enable php_gd2.dll in php.ini"; exit; }
    }
}