<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\SubscribeForm;
use Sandbox\WebsiteBundle\Entity\Subscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends Controller
{

    /**
     * @Route("/subscribe/")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeAction(Request $request)
    {
        $lang = $request->getLocale();

        //subscription attempt
        if($request->getMethod() == "POST"){
            $subscribeForm = new SubscribeForm();
            $form = $this->getForm($subscribeForm);

            $form->handleRequest($request);

            if($form->isValid()){
                //save subscription

                /** @var ObjectManager $em */
                $em = $this->getDoctrine()->getManager();

                $nodes = explode(',', $subscribeForm->getNode());

                $subscribed = false;
                $resend = false;
                $already = false;
                $sendEmail = null;
                foreach ($nodes as $nodeId) {
                    //get node
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->find($nodeId);

                    if(!$node) continue;

                    //check if subscription already exists
                    /** @var Subscription $subscription */
                    $subscription = $em->getRepository('SandboxWebsiteBundle:Subscription')
                        ->findOneBy(['email' => $subscribeForm->getEmail(), 'lang' => $lang, 'node' => $node, 'host' => $request->getHost()]);

                    if($subscription){
                        //if not active resend email?
                        if(!$subscription->getActive()){
                            //re send activation email
                            $sendEmail = $subscription;

                            $resend = true;

                        }else{
                            $already = true;
                        }
                    }else{
                        $hash = md5(md5($request->getHost() . $subscribeForm->getEmail() . $subscribeForm->getNode() . microtime()));
                        $subscription = new Subscription();
                        $subscription->setLang($lang);
                        $subscription->setActive(0);
                        $subscription->setEmail($subscribeForm->getEmail());
                        $subscription->setNode($node);
                        $subscription->setHash($hash);
                        $subscription->setHost($request->getHost());

                        //save
                        $em->persist($subscription);
                        $em->flush();

                        //send activation email
                        $sendEmail = $subscription;

                        $subscribed = true;
                    }
                }

                //send activation email
                if($sendEmail)
                    $this->sendActivationEmail($sendEmail);

                if(!$subscribed && $resend && !$already){//not subscribed and resend
                    $this->get('session')->getFlashBag()->add('info', "Subscription not active");
                    $this->get('session')->getFlashBag()->add('info', "Activation email was re send to your email.");
                }
                if(!$subscribed && $already && !$resend){
                    $this->get('session')->getFlashBag()->add('info', "Already subscribed");
                }

                if($subscribed){
                    $this->get('session')->getFlashBag()->add('info', "Subscribed successfully");
                    $this->get('session')->getFlashBag()->add('info', "Activation is send to your email");
                }

                //redirect to previous page
                $prevPage = $request->server->get('HTTP_REFERER');

                return new RedirectResponse($prevPage);
            }

        }



        return new Response("hello");
    }


    /**
     * @Route("/activate-subscription/{hash}")
     *
     * @param $hash
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function activateSubscriptionAction(Request $request, $hash)
    {
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Subscription $subscription */
        $subscription = $em->getRepository('SandboxWebsiteBundle:Subscription')
            ->findOneBy(['hash' => $hash]);

        if(!$subscription)
            throw new NotFoundHttpException("Subscription does not exist");

        $subscriptions = $em->getRepository('SandboxWebsiteBundle:Subscription')
            ->findBy(['email' => $subscription->getEmail(), 'active' => 0, 'host' => $request->getHost()]);

        foreach ($subscriptions as $subscription) {
            $subscription->setActive(1);
            $em->persist($subscription);
        }

        $em->flush();

        $this->get('session')->getFlashBag()->add('info', 'Your subscription activated');

        return new RedirectResponse($this->generateUrl('_slug'));
    }


    /**
     * @Route("/unsubscribe/{hash}")
     * @Template()
     *
     * @param $hash
     * @return array
     */
    public function unSubscribeAction(Request $request, $hash)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Subscription $subscription */
        $subscription = $em->getRepository('SandboxWebsiteBundle:Subscription')
            ->findOneBy(['hash' => $hash]);

        if(!$subscription)
            throw new NotFoundHttpException('Subscription not found');

        $email = $subscription->getEmail();

        $subscriptions = $em->getRepository('SandboxWebsiteBundle:Subscription')
            ->findBy(['email' => $email]);


        if($request->getMethod() == 'POST'){
            $ids = array_values($request->request->all());

            foreach ($subscriptions as $subscription) {
                if(in_array($subscription->getId(), $ids)){
                    $em->remove($subscription);
                }
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'Un subscribed successfully');

            $subscriptions = $em->getRepository('SandboxWebsiteBundle:Subscription')
                ->findBy(['email' => $email, 'host' => $request->getHost()]);

            if(!$subscriptions)
                return new RedirectResponse($this->generateUrl('_slug'));
        }

        /** @var Node[] $node */
        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodesByInternalName('homapage', $request->getLocale());

        $page = $node[0]->getNodeTranslation($request->getLocale())->getRef($em);

        return ['page' => $page, 'subscriptions' => $subscriptions, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @param Request $request
     * @param $page
     *
     * @return array
     *
     * @Template()
     */
    public function subscribeFormAction(Request $request, $page)
    {
        if(!$page) return new Response("");//mby change to error?

        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $node = null;
        if($page instanceof ArticlePage){
            /** @var ArticlePage $page */
            if(!$page->getPlaces() && !$page->getFromPlaces()){
                return new Response("");//mby change to error?
            }

            $nodes = [];

            foreach ($page->getPlaces() as $place) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                $nodes[$node->getId()] = $node->getId();//add unique nodes
            }
            foreach ($page->getFromPlaces() as $place) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                $nodes[$node->getId()] = $node->getId();//add unique nodes
            }

            $node = implode(',', array_keys($nodes));


        }else if($page instanceof PlaceOverviewPage){
            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($page);
            $node = $node->getId();
        }

        if(!$node) // no nodes were found
            return new Response("");//mby change to error?

        $subscribeForm = new SubscribeForm();
        $subscribeForm->setNode($node);

        $form = $this->getForm($subscribeForm);
        return ['form' => $form->createView()];
    }


    private function getForm(SubscribeForm $subscribeForm)
    {
        return $this->createFormBuilder($subscribeForm)
            ->setAction($this->generateUrl('sandbox_website_subscription_subscribe'))
            ->add('node', 'hidden')
            ->add('email', 'email')
            ->add('submit', 'submit')
            ->getForm();
    }

    /**
     * @param $subscription
     */
    private function sendActivationEmail(Subscription $subscription)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Subscription activation')
            ->setFrom('travelnews.daily@gmail.com')
            ->setTo($subscription->getEmail())
            ->setBody(
                $this->renderView(
                    'SandboxWebsiteBundle:Subscription:activation.html.twig',
                    array('subscription' => $subscription)
                )
                , 'text/html');
        $this->get('mailer')->send($message);
    }


    /**
     * @param Request $request
     * @param $page
     * @return array
     *
     * @Template()
     */
    public function subscribeTreeAction(Request $request, $page)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        $countryRootNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy(
                [
                    'parent' => 1,
                    'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                    'deleted' => 0
                ]);

        if(!$countryRootNode) throw new NotFoundHttpException('Country root node not found');

        $places = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getActiveOverviewPages($request->getLocale(), $host, $countryRootNode->getId());

        $result = [];
        foreach ($places as $place) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($place);

            $children = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                ->getActiveOverviewPages($request->getLocale(), $host, $node->getId());

            $subChld = [];
            foreach ($children as $child) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($child);

                $subChildren = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->getActiveOverviewPages($request->getLocale(), $host, $node->getId());

                $subChld[] = [ 'place' => $child, 'children' => $subChildren];
            }

            $result[] = [
                'place' => $place,
                'children' => $subChld
            ];
        }

        $form = new SubscribeForm();
        $form = $this->getForm($form);

        return [ 'places' => $result , 'form' => $form->createView() ];
    }
}
