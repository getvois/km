<?php
namespace Sandbox\WebsiteBundle\EventListener;


use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Event\NodeEvent;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Subscription;
use Sandbox\WebsiteBundle\Helper\FacebookHelper;
use Symfony\Component\DependencyInjection\Container;

class PublishListener {
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }
    public function onPostPublish(NodeEvent $nodeEvent)
    {
        $page = $nodeEvent->getPage();
        $lang = $nodeEvent->getNodeTranslation()->getLang();

        if($page instanceof IPlaceFromTo){
            $page->getPlaces();
            $page->getFromPlaces();

            $emails = [];

            $this->sendEmails($page->getPlaces(), $page, $lang, $emails);
            $this->sendEmails($page->getFromPlaces(), $page, $lang, $emails);

        }


        if($page instanceof NewsPage || $page instanceof ArticlePage){
            //if checkbox post on fb is checked
            if($page->isPostOnFb()){
                //post on fb
                $fb = new FacebookHelper();
                /** @var Host $host */
                foreach ($page->getHosts() as $host) {
                    $fb->postOnWall($page, $this->em, $host);
                }
            }
        }

    }


    /**
     * @param $page
     * @param $email
     * @param $subscription
     */
    private function sendEmail($page, $email, $subscription)
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $message = \Swift_Message::newInstance()
            ->setSubject('Subscription activation')
            ->setFrom('travelnews.daily@gmail.com')
            ->setTo($email)
            ->setBody(
                $this->container->get('templating')->render(
                    'SandboxWebsiteBundle:Subscription:letter.html.twig',
                    array('page' => $page, 'subscription' => $subscription)
                )
                , 'text/html');
        $this->container->get('mailer')->send($message);
    }

    /**
     * @param $places
     * @param $lang
     * @param $emails
     */
    private function sendEmails($places, $page, $lang, &$emails)
    {
        foreach ($places as $placePage) {
            //get node from place( node in subscriptions)
            /** @var Node $node */
            $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($placePage);

            while ($node) {
                /** @var Subscription[] $subscriptions */
                $subscriptions = $this->em->getRepository('SandboxWebsiteBundle:Subscription')
                    ->findBy(['node' => $node, 'active' => true, 'lang' => $lang]);

                if ($subscriptions) {
                    //send emails to subscribers
                    foreach ($subscriptions as $subscription) {
                        $email = $subscription->getEmail();
                        //check if email was already send to this email
                        //(multiple times traveling up in tree)
                        if (!in_array($email, $emails)) {
                            $this->sendEmail($page, $email, $subscription);
                            $emails[] = $email;
                        }
                    }
                }

                //set current node to parent
                $node = $node->getParent();
            }

        }
    }
} 