<?php

namespace Sandbox\WebsiteBundle\DataFixtures\ORM\DefaultSiteGenerator;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Kunstmaan\AdminBundle\Entity\DashboardConfiguration;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Helper\RemoteVideo\RemoteVideoHelper;
use Kunstmaan\MediaBundle\Helper\Services\MediaCreatorService;
use Kunstmaan\NodeBundle\Helper\Services\PageCreatorService;
use Kunstmaan\PagePartBundle\Helper\Services\PagePartCreatorService;
use Kunstmaan\TranslatorBundle\Entity\Translation;

use Sandbox\WebsiteBundle\Entity\Pages\ContentPage;
use Sandbox\WebsiteBundle\Entity\Pages\HomePage;
use Sandbox\WebsiteBundle\Entity\Pages\FormPage;
use Sandbox\WebsiteBundle\Entity\Satellite;
use Sandbox\WebsiteBundle\Entity\Pages\SatelliteOverviewPage;

/**
 * DefaultSiteFixtures
 */
class DefaultSiteFixtures extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * Username that is used for creating pages
     */
    const ADMIN_USERNAME = 'Admin';

    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var PageCreatorService
     */
    private $pageCreator;

    /**
     * @var PagePartCreatorService
     */
    private $pagePartCreator;

    /**
     * @var MediaCreatorService
     */
    private $mediaCreator;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->pageCreator = $this->container->get('kunstmaan_node.page_creator_service');
        $this->pagePartCreator = $this->container->get('kunstmaan_pageparts.pagepart_creator_service');
        $this->mediaCreator = $this->container->get('kunstmaan_media.media_creator_service');

        $this->createTranslations();
        $this->createMedia();
        $this->createHomePage();
        $this->createContentPages();
        $this->createAdminListPages();
        // $this->createStylePage();
        $this->createFormPage();
        $this->createDashboard();
    }

    /**
     * Create the dashboard
     */
    private function createDashboard()
    {
        /** @var $dashboard DashboardConfiguration */
        $dashboard = $this->manager->getRepository("KunstmaanAdminBundle:DashboardConfiguration")->findOneBy(array());
        if (is_null($dashboard)) {
            $dashboard = new DashboardConfiguration();
        }
        $dashboard->setTitle("Dashboard");
        $dashboard->setContent('<div class="alert alert-info"><strong>Important: </strong>please change these items to the graphs of your own site!</div><iframe src="https://rpm.newrelic.com/public/charts/jjPIEE7OHz9" width="100%" height="300" scrolling="no" frameborder="no"></iframe><iframe src="https://rpm.newrelic.com/public/charts/hmDWR0eUNTo" width="100%" height="300" scrolling="no" frameborder="no"></iframe><iframe src="https://rpm.newrelic.com/public/charts/fv7IP1EmbVi" width="100%" height="300" scrolling="no" frameborder="no"></iframe>');
        $this->manager->persist($dashboard);
        $this->manager->flush();
    }

    /**
     * Create a Homepage
     */
    private function createHomePage()
    {
        $homePage = new HomePage();
        $homePage->setTitle('Home');

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Home');
            $translation->setSlug('');
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Home');
            $translation->setSlug('');
        });

        $options = array(
            'parent' => null,
            'page_internal_name' => 'homepage',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($homePage, $translations, $options);

        $pageparts = array();
        $pageparts['left_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'First column heading',
                'setNiv'   => 1
            )
        );
        $pageparts['left_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.'
            )
        );
        $pageparts['middle_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Second column heading',
                'setNiv'   => 1
            )
        );
        $pageparts['middle_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable.'
            )
        );
        $pageparts['right_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Third column heading',
                'setNiv'   => 1
            )
        );
        $pageparts['right_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from "de Finibus Bonorum et Malorum" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('homepage', $pageparts, 'en');

        $pageparts = array();
        $pageparts['left_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Eerste title',
                'setNiv'   => 1
            )
        );
        $pageparts['left_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'Lorem Ipsum is slechts een proeftekst uit het drukkerij- en zetterijwezen. Lorem Ipsum is de standaard proeftekst in deze bedrijfstak sinds de 16e eeuw, toen een onbekende drukker een zethaak met letters nam en ze door elkaar husselde om een font-catalogus te maken.'
            )
        );
        $pageparts['middle_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Tweede title',
                'setNiv'   => 1
            )
        );
        $pageparts['middle_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'Er zijn vele variaties van passages van Lorem Ipsum beschikbaar maar het merendeel heeft te lijden gehad van wijzigingen in een of andere vorm, door ingevoegde humor of willekeurig gekozen woorden die nog niet half geloofwaardig ogen.'
            )
        );
        $pageparts['right_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Derde titel',
                'setNiv'   => 1
            )
        );
        $pageparts['right_column'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => 'Het standaard stuk van Lorum Ipsum wat sinds de 16e eeuw wordt gebruikt is hieronder, voor wie er interesse in heeft, weergegeven. Secties 1.10.32 en 1.10.33 van "de Finibus Bonorum et Malorum" door Cicero zijn ook weergegeven in hun exacte originele vorm, vergezeld van engelse versies van de 1914 vertaling door H. Rackham.'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('homepage', $pageparts, 'nl');
    }

    /**
     * Create a ContentPage
     */
    private function createContentPages()
    {
        $nodeRepo = $this->manager->getRepository('KunstmaanNodeBundle:Node');
        $homePage = $nodeRepo->findOneBy(array('internalName' => 'homepage'));

        $contentPage = new ContentPage();
        $contentPage->setTitle('Satellite');

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Satellite');
            $translation->setSlug('satellite');
            $translation->setWeight(20);
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Satelliet');
            $translation->setSlug('satelliet');
            $translation->setWeight(20);
        });

        $options = array(
            'parent' => $homePage,
            'page_internal_name' => 'satellite',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($contentPage, $translations, $options);

        // Add images to database
        $folder = $this->manager->getRepository('KunstmaanMediaBundle:Folder')->findOneBy(array('rel' => 'image'));
        $imgDir = dirname(__FILE__).'/../../../Resources/public/files/content/';
        $satelliteMedia = $this->mediaCreator->createFile($imgDir.'satellite.jpg', $folder->getId());
        $orbitsMedia = $this->mediaCreator->createFile($imgDir.'orbits.jpg', $folder->getId());

        // Add pageparts
        $pageparts = array();
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Satellite (artificial)',
                'setNiv'   => 1
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>A <b>satellite</b> is an object that orbits another object. In space, satellites may be made by man, or they may be natural. The moon is a natural satellite that orbits the Earth. Most man-made satellites also orbit the Earth, but some orbit other planets, such as Saturn, Venus or Mars, or the moon.</p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'History',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\MediaPagePartBundle\Entity\ImagePagePart',
            array(
                'setMedia' => $satelliteMedia
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => "<p>The idea of a man-made satellite has been around for a long time. When Isaac Newton was thinking about gravity, he came up with the thought experiment called Newton's cannonball. He wondered what would happen if a cannonball was shot from a tall mountain. If fired at just the right speed (and ignoring the friction of air), he realized it would orbit the Earth. Later, Jules Verne wrote about a satellite in 1879 in a book called Begum's Fortune.</p>
                                 <p>In 1903, Konstantin Tsiolkovsky wrote Means of Reaction Devices (in Russian: Исследование мировых пространств реактивными приборами), which was the first serious study on how to use rockets to launch spacecraft. He calculated the speed needed to reach orbit around the Earth (at 8 km/s). He also wrote that a multi-stage rocket, using liquid fuel could reach that speed. He recommended liquid hydrogen and liquid oxygen, though other fuels could be used. He was correct on all of these points.</p>
                                 <p>The English science fiction writer Arthur C. Clarke is given the credit of coming up with the idea of the communication satellite in 1945. He described in detail the possible use of satellites for mass communication, how to launch satellites, what orbits they could use, and the benefits of having a network of world-circling satellites.</p>
                                 <p>The world's first artificial satellite, the Sputnik 1, was launched by the Soviet Union on October 4, 1957. This surprised the world, and the United States quickly worked to launch their own satellite, starting the space race. Sputnik 2 was launched on November 3, 1957 and carried the first living passenger into orbit, a dog named Laika. The United States launched their first satellite, called Explorer 1 on January 31, 1958. The UK launched its first satellite in 1962.</p>
                                 <p>Since then, thousands of satellites have been launched into orbit around the Earth. Some satellites, notably space stations, have been launched in parts and assembled in orbit.</p>"
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Satellites orbiting now',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => "<p>Artificial satellites come from more than 50 countries and have used the satellite launching capabilities of ten nations. A few hundred satellites are currently working, but thousands of unused satellites and satellite fragments orbit the Earth as space debris. The largest satellite is the International Space Station, which was put together in space from sections made by several different countries (including the organizations of NASA, ESA, JAXA and RKA). It usually has a crew of six astronauts or cosmonauts living on board. It is permanently occupied, but the crews change. The Hubble Space Telescope has been repaired and updated by astronauts in space several times.</p>
                                 <p>There are also man-made satellites orbiting something other than the Earth. The Mars Reconnaissance Orbiter is orbiting Mars. Cassini-Huygens is orbiting Saturn. Venus Express, run by the ESA, is orbiting Venus. Two GRAIL satellites orbited the moon until December 2012. There are plans to launch a satellite in 2017 called the Solar Orbiter (SolO) that will orbit the sun.</p>
                                 <p>Man-made satellites have several main uses:</p>
                                 <ul>
                                 <li>Scientific Investigation</li>
                                 <li>Earth Observation - including weather forecasting and tracking storms and pollution</li>
                                 <li>Communications - including satellite television and telephone calls</li>
                                 <li>Navigation - including Global Positioning System (GPS)</li>
                                 <li>Military - including spy photography and communications (nuclear weapons are not allowed in space)</li>
                                 </ul>"
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Orbits',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\MediaPagePartBundle\Entity\ImagePagePart',
            array(
                'setMedia' => $orbitsMedia
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => "<p>Most of the man-made satellites are in a low Earth orbit (LEO) or a geostationary orbit. To stay in orbit, the satellite's sideways speed must balance the force of gravity. Closer to the Earth, in LEO, the satellites must move faster to stay in orbit. Low orbits work well for satellites that take pictures of the Earth. It is easier to put a satellite in low Earth orbit, but the satellite appears to move when viewed from Earth. This means a satellite dish (a type of antenna) must be always moving in order to send or receive communications with that satellite. This works well for GPS satellites - receivers on Earth use the satellite's changing position and precise time (and a type of antenna that does not have to be pointed) to find where on Earth the receiver is. But constantly changing positions does not work for satellite TV and other types of satellites that send and receive a lot of information. Those need to be in geostationary orbit.</p>
                                 <p>A satellite in a geostationary orbit moves around the Earth as fast as the Earth spins, so from the ground it looks like it is stationary (not moving). To move this way, the satellite must be straight above the equator, and 35,786 kilometers (22,236 miles) above the ground. Satellites in low Earth orbit are often less than one thousand kilometers above the ground. They move much faster. Many are in tilted orbits (they swing above and below the equator), so they can communicate, or see what is happening in other areas, depending on what they are used for.</p>"
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'References',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p><a href="http://simple.wikipedia.org/wiki/Satellite_(artificial)">Wikipedia</a></p>'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('satellite', $pageparts, 'en');

        $pageparts = array();
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Kunstmaan (satelliet)',
                'setNiv'   => 1
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>Een <b>kunstmaan</b> of <b>satelliet</b> is een door mensen gemaakt object in een baan om een hemellichaam. Kunstmanen zijn onbemande toestellen die door de mens in een baan zijn gebracht. Natuurlijke manen zijn meestal objecten met de structuur van een kleine planeet of planetoïde die door de zwaartekracht van de planeet in hun baan worden gehouden.</p>'
            )
        );

        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Historie',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>De eerste succesvol in een baan om de aarde gebrachte satelliet is de Spoetnik 1 van de Sovjet-Unie op 4 oktober 1957. Vaak wordt deze datum gezien als het begin van het ruimtevaarttijdperk. De eerste Amerikaanse satelliet die in een baan om te aarde gebracht werd was de Explorer 1.</p>
                                 <p>De eerste satelliet in een baan rond Mars was de Amerikaanse Mariner 9 op 13 november 1971, slechts enkele weken later gevolgd door de Mars 2 en de Mars 3 (27 november en 2 december 1971) van de Sovjet-Unie.</p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Classificatie',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\MediaPagePartBundle\Entity\ImagePagePart',
            array(
                'setMedia' => $satelliteMedia
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>Afhankelijk van de toepassing kunnen satellieten als volgt worden geclassificeerd:</p>
                                <ul>
                                <li>Communicatiesatelliet: verzorging van telefoon, radio, televisie, internet over lange afstanden zoals Artemis</li>
                                <li>Navigatiesatelliet: voor plaatsbepaling op aarde met bijvoorbeeld GPS of Galileo</li>
                                <li>Observatiesatelliet: observatie van bijvoorbeeld milieuverontreiniging, maken van landkaarten en observeren van het heelal, bijvoorbeeld Envisat of ANS</li>
                                <li>Onderzoekssatelliet: voor wetenschappelijk onderzoek bijvoorbeeld naar gewichtloosheid, Sloshsat-FLEVO</li>
                                <li>Spionagesatelliet: veelal militaire toepassingen, bv. CORONA (Satelliet)</li>
                                <li>Weersatelliet: toegepast bij het doen van weersvoorspellingen, bijvoorbeeld Meteosat</li>
                                </ul>
                                <p>Een aparte categorie vormen de ruimtestations die in zekere zin ook satellieten zijn.</p>
                                <p>Satellieten worden ook geclassificeerd naar massa (inclusief brandstof):</p>
                                <ul>
                                <li>Minisatelliet of gewoon "kleine satelliet": 100 tot 500 kg</li>
                                <li>Microsatelliet: 10 tot 100 kg</li>
                                <li>Nanosatelliet: 1 tot 10 kg</li>
                                <li>Picosatelliet: 100 g tot 1 kg</li>
                                <li>Femtosatelliet: 10 tot 100 g - bevinden zich in de testfase</li>
                                </ul>
                                <p>Een of meer (zeer) kleine satellieten worden soms aanvullend, met dezelfde draagraket, gelanceerd bij de lancering van een gewone satelliet (meeliften, piggyback ride), zie bijvoorbeeld de eerste lancering van de Antares raket. Verder is bijvoorbeeld in ontwikkeling raket LauncherOne die eerst met de White Knight Two op 15 km hoogte wordt gebracht en vandaar gelanceerd wordt (zie ook hieronder); afhankelijk van de baan waarin een satelliet moet worden gebracht, kan deze een satelliet van 100 tot 250 kg lanceren. Ook in ontwikkeling is de SWORDS, een kleine raket die vanaf de grond een satelliet van 25 kg kan lanceren.</p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Lancering',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>De traditionele manier om een satelliet in een baan om de aarde te brengen is door middel van een lanceerraket, zoals de Europese Ariane-raket. Afhankelijk van de voortstuwingskracht van de raket en van het gewicht van de satellieten, kunnen soms meerdere satellieten tegelijk gelanceerd worden. Na de lancering komt een satelliet meestal in een tijdelijke overgangsbaan, om daarna door zijn eigen motor naar de gewenste definitieve baan te worden gestuwd.</p>
                                 <p>Een andere manier om satellieten in de ruimte te brengen, is ze aan boord van een ruimteveer mee te nemen en in de ruimte uit te zetten, zoals met de Hubble-ruimtetelescoop is gebeurd.</p>
                                 <p>Een raket kan ook vanaf een vliegtuig gelanceerd worden, dat de raket tot op een grote hoogte (ongeveer 12 kilometer) brengt en daar lanceert. Dit heeft als voordeel dat de raket zelf kleiner, en dus goedkoper, kan zijn, omdat ze slechts een deel van de zwaartekracht van de aarde moet overwinnen. De commerciële ruimtevaartfirma Orbital voert dergelijke lanceringen uit met de Pegasusraket die vanaf een Lockheed L-1011 TriStar wordt gelanceerd.</p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Plaatsing',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\MediaPagePartBundle\Entity\ImagePagePart',
            array(
                'setMedia' => $orbitsMedia
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p>Een satelliet kan in een geostationaire of niet geostationaire baan om de aarde worden gebracht. Een geostationair geplaatste satelliet hangt op een hoogte van ongeveer 36.000 km op een vast punt boven de evenaar. Op die hoogte is de omlooptijd van de satelliet namelijk exact gelijk aan de rotatiesnelheid van de aarde om haar eigen as (ongeveer 24 uur). Het idee van geostationaire kunstmanen werd oorspronkelijk door de sciencefictionschrijver Arthur C. Clarke geopperd. Geostationaire satellieten zijn bij uitstek geschikt voor observatie en telefoon- en andere communicatieverbindingen, omdat antennes op aarde naar een vast punt gericht kunnen blijven. Wel is de vertraging in de communicatie iets groter (ongeveer 0,25 seconde) dan voor een satelliet in een lagere baan. Ook staat op zeer hoge breedtegraden (dicht bij de polen) de satelliet nauwelijks boven de horizon.</p>
                                 <p>Een niet-geostationair geplaatste satelliet beweegt met een bepaalde snelheid ten opzichte van het aardoppervlak. Dit komt doordat de hoeksnelheid van de kunstmaan groter (op lage hoogte) of kleiner (op grote hoogte) is dan de hoeksnelheid van de aardrotatie. Voor elke cirkelbeweging van een kunstmaan dient de middelpuntzoekende kracht gelijk te zijn aan de zwaartekracht. Naarmate de baan hoger is, is de zwaartekracht lager. Als gevolg daarvan is in hogere banen de baansnelheid lager.</p>
                                 <p>Satellietbanen kunnen cirkelvormig of elliptisch zijn, met de aarde in een brandpunt van de ellips. In een cirkelvormige baan blijft de satelliet altijd even hoog boven het aardoppervlak; een ellipsvormige baan wordt gekenmerkt door de laagste hoogte (het perigeum) en de grootste hoogte (het apogeum). De omlooptijd van de satelliet is de tijd nodig om één volledige baan uit te voeren; hierbij geldt dat hoe hoger de satelliet zich boven het aardoppervlak bevindt, hoe langer de omlooptijd is.</p>
                                 <p>Daarnaast wordt een satellietbaan gekenmerkt door de inclinatie, dat wil zeggen, de hoek ervan met de evenaar. Een polaire baan staat loodrecht op de evenaar (inclinatie 90°) en loopt dus over de twee polen; dit heeft als voordeel, dat de satelliet het volledige aardoppervlak kan overvliegen en observeren. Dit is onder meer het geval voor de commerciële satelliet IKONOS die gedetailleerde beelden van elk deel van de aarde kan maken. Geostationaire satellieten hebben een inclinatie van 0° (ze blijven boven de evenaar).</p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Referenties',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
            array(
                'setContent' => '<p><a href="http://nl.wikipedia.org/wiki/Kunstmaan">Wikipedia</a></p>'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('satellite', $pageparts, 'nl');
    }

    /**
     * Create a ContentPages based on an admin list
     */
    private function createAdminListPages()
    {
        $nodeRepo = $this->manager->getRepository('KunstmaanNodeBundle:Node');
        $satellitePage = $nodeRepo->findOneBy(array('internalName' => 'satellite'));

        $satelliteOverviewPage = new SatelliteOverviewPage();
        $satelliteOverviewPage->setTitle('Communication satellites');
        $satelliteOverviewPage->setType(Satellite::TYPE_COMMUNICATION);

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Communication satellites');
            $translation->setSlug('communication-satellites');
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Communicatie satellieten');
            $translation->setSlug('communicatie-satellieten');
        });

        $options = array(
            'parent' => $satellitePage,
            'page_internal_name' => 'communication-satellites',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($satelliteOverviewPage, $translations, $options);

        $satelliteOverviewPage = new SatelliteOverviewPage();
        $satelliteOverviewPage->setTitle('Climate research satellites');
        $satelliteOverviewPage->setType(Satellite::TYPE_CLIMATE);

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Climate research satellites');
            $translation->setSlug('climate-research-satellites');
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Klimatologische onderzoekssatellieten');
            $translation->setSlug('klimatologische-onderzoekssatellieten');
        });

        $options = array(
            'parent' => $satellitePage,
            'page_internal_name' => 'climate-research-satellites',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($satelliteOverviewPage, $translations, $options);

        $satelliteOverviewPage = new SatelliteOverviewPage();
        $satelliteOverviewPage->setTitle('Passive satellites');
        $satelliteOverviewPage->setType(Satellite::TYPE_PASSIVE);

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Passive satellites');
            $translation->setSlug('passive-satellites');
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Passieve satellieten');
            $translation->setSlug('passieve-satellieten');
        });

        $options = array(
            'parent' => $satellitePage,
            'page_internal_name' => 'passive-satellites',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($satelliteOverviewPage, $translations, $options);

        $list = array(
            array('Sputnik 1', '1957-10-04', 'http://en.wikipedia.org/wiki/Sputnik_1', 84, Satellite::TYPE_COMMUNICATION),
            array('Echo 1', '1960-08-12', 'http://en.wikipedia.org/wiki/Echo_satellite', 180, Satellite::TYPE_COMMUNICATION),
            array('Telstar 1', '1962-07-10', 'http://en.wikipedia.org/wiki/Telstar', 70, Satellite::TYPE_COMMUNICATION),
            array('Intelsat I', '1965-04-06', 'http://en.wikipedia.org/wiki/Intelsat_I', 149, Satellite::TYPE_COMMUNICATION),

            array('ACRIMSAT', '1999-12-20', 'http://en.wikipedia.org/wiki/ACRIMSAT', 288, Satellite::TYPE_CLIMATE),
            array('Terra', '1999-12-18', 'http://en.wikipedia.org/wiki/Terra_(satellite)', 4864, Satellite::TYPE_CLIMATE),
            array('GRACE', '2002-03-14', 'http://en.wikipedia.org/wiki/Gravity_Recovery_and_Climate_Experiment', 487, Satellite::TYPE_CLIMATE),
            array('Landsat 7', '1999-04-15', 'http://en.wikipedia.org/wiki/Landsat-7', 1973, Satellite::TYPE_CLIMATE),
            array('SORCE', '2003-01-25', 'http://en.wikipedia.org/wiki/SORCE', 315, Satellite::TYPE_CLIMATE),

            array('LARES', '2012-02-13', 'http://en.wikipedia.org/wiki/LARES_(satellite)', 400, Satellite::TYPE_PASSIVE),
            array('LAGEOS 1', '1976-05-04', 'http://en.wikipedia.org/wiki/LAGEOS', 411, Satellite::TYPE_PASSIVE),
        );
        foreach ($list as $info) {
            $satellite = new Satellite();
            $satellite->setName($info[0]);
            $satellite->setLaunched(new \DateTime($info[1]));
            $satellite->setLink($info[2]);
            $satellite->setWeight($info[3]);
            $satellite->setType($info[4]);

            $this->manager->persist($satellite);
        }

        $this->manager->flush();
    }

    /**
     * Create a ContentPage with some styled components
     */
    private function createStylePage()
    {
        $nodeRepo = $this->manager->getRepository('KunstmaanNodeBundle:Node');
        $homePage = $nodeRepo->findOneBy(array('internalName' => 'homepage'));

        $contentPage = new ContentPage();
        $contentPage->setTitle('Home');

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Styles');
            $translation->setSlug('styles');
            $translation->setWeight(40);
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Styles');
            $translation->setSlug('styles');
            $translation->setWeight(40);
        });

        $options = array(
            'parent' => $homePage,
            'page_internal_name' => 'styles',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $this->pageCreator->createPage($contentPage, $translations, $options);

        $pageparts = array();
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Buttons',
                'setNiv'   => 1
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Sizes',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\RawHTMLPagePart',
            array(
                'setContent' => '<p>
                                 <button class="btn btn-mini">Mini button</button>
                                 <button class="btn btn-small">Small button</button>
                                 <button class="btn">Normal</button>
                                 <button class="btn btn-large">Large button</button>
                                 </p>'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\HeaderPagePart',
            array(
                'setTitle' => 'Styles',
                'setNiv'   => 2
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\RawHTMLPagePart',
            array(
                'setContent' => '<p>
                                 <button class="btn btn-large">Normal</button>
                                 <button class="btn btn-large btn-primary">Primary</button>
                                 <button class="btn btn-large btn-info">Info</button>
                                 <button class="btn btn-large btn-success">Success</button>
                                 <button class="btn btn-large btn-danger">Danger</button>
                                 <button class="btn btn-large btn-warning">Warning</button>
                                 <button class="btn btn-large btn-inverse">Inverse</button>
                                 <button class="btn btn-large btn-link">Link</button>
                                 </p>'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('styles', $pageparts, 'en');
        $this->pagePartCreator->addPagePartsToPage('styles', $pageparts, 'nl');
    }

    /**
     * Create a FormPage
     */
    private function createFormPage()
    {
        $nodeRepo = $this->manager->getRepository('KunstmaanNodeBundle:Node');
        $homePage = $nodeRepo->findOneBy(array('internalName' => 'homepage'));

        $formPage = new FormPage();
        $formPage->setTitle('Contact form');

        $translations = array();
        $translations[] = array('language' => 'en', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Contact');
            $translation->setSlug('contact');
            $translation->setWeight(60);
        });
        $translations[] = array('language' => 'nl', 'callback' => function($page, $translation, $seo) {
            $translation->setTitle('Contact');
            $translation->setSlug('contact');
            $translation->setWeight(60);
        });

        $options = array(
            'parent' => $homePage,
            'page_internal_name' => 'contact',
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => self::ADMIN_USERNAME
        );

        $node = $this->pageCreator->createPage($formPage, $translations, $options);

        $nodeTranslation = $node->getNodeTranslation('en', true);
        $nodeVersion = $nodeTranslation->getPublicNodeVersion();
        $page = $nodeVersion->getRef($this->manager);
        $page->setThanks("<p>We have received your submission.</p>");
        $this->manager->persist($page);

        $nodeTranslation = $node->getNodeTranslation('nl', true);
        $nodeVersion = $nodeTranslation->getPublicNodeVersion();
        $page = $nodeVersion->getRef($this->manager);
        $page->setThanks("<p>Bedankt, we hebben je bericht succesvol ontvangen.</p>");
        $this->manager->persist($page);

        $pageparts = array();
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\SingleLineTextPagePart',
            array(
                'setLabel' => 'Name',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Name is required'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\EmailPagePart',
            array(
                'setLabel' => 'Email',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Email is required',
                'setErrorMessageInvalid' => 'Fill in a valid email address'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\ChoicePagePart',
            array(
                'setLabel' => 'Subject',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Subject is required',
                'setChoices' => "I want to make a website with the Kunstmaan bundles \n I'm testing the website \n I want to get a quote for a website built by Kunstmaan"
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\MultiLineTextPagePart',
            array(
                'setLabel' => 'Message',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Message is required'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\SubmitButtonPagePart',
            array(
                'setLabel' => 'Send'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('contact', $pageparts, 'en');

        $pageparts = array();
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\SingleLineTextPagePart',
            array(
                'setLabel' => 'Naam',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Naam is verplicht'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\EmailPagePart',
            array(
                'setLabel' => 'Email',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Email is verplicht',
                'setErrorMessageInvalid' => 'Vul een geldif email adres in'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\ChoicePagePart',
            array(
                'setLabel' => 'Onderwerp',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Onderwerp is verplicht',
                'setChoices' => "Ik wil een website maken met de Kunstmaan bundles \n Ik ben een website aan het testen \n Ik wil dat Kunstmaan een website voor mij maakt"
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\MultiLineTextPagePart',
            array(
                'setLabel' => 'Bericht',
                'setRequired' => true,
                'setErrorMessageRequired' => 'Bericht is verplicht'
            )
        );
        $pageparts['main'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\FormBundle\Entity\PageParts\SubmitButtonPagePart',
            array(
                'setLabel' => 'Verzenden'
            )
        );

        $this->pagePartCreator->addPagePartsToPage('contact', $pageparts, 'nl');

        $this->manager->flush();
    }

    /**
     * Insert all translations
     */
    private function createTranslations()
    {
        // SplashPage
        $trans['lang_chooser.welcome']['en'] = 'Welcome, continue in English';
        $trans['lang_chooser.welcome']['fr'] = 'Bienvenu, continuer en Français';
        $trans['lang_chooser.welcome']['nl'] = 'Welkom, ga verder in het Nederlands';
        $trans['lang_chooser.welcome']['de'] = 'Willkommen, gehe weiter in Deutsch';

        // AdminList page with satellites
        $trans['satellite.name']['en'] = 'name';
        $trans['satellite.launched']['en'] = 'launched';
        $trans['satellite.weight']['en'] = 'launch mass';
        $trans['satellite.'.Satellite::TYPE_COMMUNICATION]['en'] = 'Communication satellites';
        $trans['satellite.'.Satellite::TYPE_CLIMATE]['en'] = 'Climate satellites';
        $trans['satellite.name']['nl'] = 'naam';
        $trans['satellite.launched']['nl'] = 'lanceringsdatum';
        $trans['satellite.weight']['nl'] = 'gewicht';
        $trans['satellite.'.Satellite::TYPE_COMMUNICATION]['nl'] = 'Communicatie satellieten';
        $trans['satellite.'.Satellite::TYPE_CLIMATE]['nl'] = 'Klimatologische satellieten';

        $trans['article.readmore']['en'] = 'Read more';
        $trans['article.readmore']['nl'] = 'Lees meer';

        $trans['results']['en'] = 'results';
        $trans['results']['nl'] = 'resultaten';
        $trans['search']['en'] = 'search';
        $trans['search']['nl'] = 'zoeken';
        $trans['search.looking_for']['en'] = 'You were looking for';
        $trans['search.looking_for']['nl'] = 'U zocht naar';

        $translationId = $this->manager->getRepository('KunstmaanTranslatorBundle:Translation')->getUniqueTranslationId();
        foreach ($trans as $key => $array) {
            foreach ($array as $lang => $value) {
                $t = new Translation();
                $t->setKeyword($key);
                $t->setLocale($lang);
                $t->setText($value);
                $t->setDomain('messages');
                $t->setCreatedAt(new \DateTime());
                $t->setFlag(Translation::FLAG_NEW);
                $t->setTranslationId($translationId);

                $this->manager->persist($t);
            }
            $translationId++;
        }

        $this->manager->flush();
    }

    /**
     * Create some dummy media files
     */
    private function createMedia()
    {
        // Add images to database
        $imageFolder = $this->manager->getRepository('KunstmaanMediaBundle:Folder')->findOneBy(array('rel' => 'image'));
        $filesFolder = $this->manager->getRepository('KunstmaanMediaBundle:Folder')->findOneBy(array('rel' => 'files'));
        $publicDir = dirname(__FILE__).'/../../../Resources/public/';
        $this->mediaCreator->createFile($publicDir.'img/general/logo.png', $imageFolder->getId());
        $this->mediaCreator->createFile($publicDir.'img/general/demo-logo.png', $imageFolder->getId());
        $this->mediaCreator->createFile($publicDir.'files/dummy/sample.pdf', $filesFolder->getId());

        // Create dummy video folder and add dummy videos
        {
            $videoFolder = $this->manager->getRepository('KunstmaanMediaBundle:Folder')->findOneBy(array('rel' => 'video'));
            $this->createVideoFile('Kunstmaan', 'WPx-Oe2WrUE', $videoFolder);
        }
    }

    /**
     * Create a video file record in the database.
     *
     * @param $name
     * @param $code
     * @param $folder
     * @return Media
     */
    private function createVideoFile($name, $code, $folder)
    {
        // Hack for media bundle issue
        $dir = dirname($this->container->get('kernel')->getRootDir());
        chdir($dir . '/web');
        $media = new Media();
        $media->setFolder($folder);
        $media->setName($name);
        $helper = new RemoteVideoHelper($media);
        $helper->setCode($code);
        $helper->setType('youtube');
        $this->manager->getRepository('KunstmaanMediaBundle:Media')->save($media);
        chdir($dir);

        return $media;
    }

    /**
     * Get the order of this fixture
     *
     * @return int
     */
    public function getOrder()
    {
        return 51;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

}
