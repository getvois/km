<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
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

                $em = $this->getDoctrine()->getManager();

                //get node
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->find($subscribeForm->getNode());

                if(!$node)
                    throw new NotFoundHttpException("Node not found");

                //check if subscription already exists
                /** @var Subscription $subscription */
                $subscription = $em->getRepository('SandboxWebsiteBundle:Subscription')
                    ->findOneBy(['email' => $subscribeForm->getEmail(), 'lang' => $lang, 'node' => $node]);

                if($subscription){
                    //if not active resend email?
                    if(!$subscription->getActive()){
                        //re send activation email
                        $this->sendActivationEmail($subscription);

                        $this->get('session')->getFlashBag()->add('info', "Subscription not active");
                        $this->get('session')->getFlashBag()->add('info', "Activation email was re send to your email.");

                    }else{
                        $this->get('session')->getFlashBag()->add('info', "Already subscribed");
                    }
                }else{
                    $hash = md5(md5($subscribeForm->getEmail() . $subscribeForm->getNode() . microtime()));
                    $subscription = new Subscription();
                    $subscription->setLang($lang);
                    $subscription->setActive(0);
                    $subscription->setEmail($subscribeForm->getEmail());
                    $subscription->setNode($node);
                    $subscription->setHash($hash);

                    //save
                    $em->persist($subscription);
                    $em->flush();

                    //send activation email
                    $this->sendActivationEmail($subscription);

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
    public function activateSubscriptionAction($hash)
    {
        $em = $this->getDoctrine()->getManager();

        $subscription = $em->getRepository('SandboxWebsiteBundle:Subscription')
            ->findOneBy(['hash' => $hash]);

        if(!$subscription)
            throw new NotFoundHttpException("Subscription does not exist");

        $subscription->setActive(1);

        $em->persist($subscription);
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
                ->findBy(['email' => $email]);

            if(!$subscriptions)
                return new RedirectResponse($this->generateUrl('_slug'));
        }


        return ['subscriptions' => $subscriptions, 'lang' => $lang, 'em' => $em];
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

        $entityName = "";
        //get entity name
        if($page instanceof PlaceOverviewPage){
            $entityName = 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage';
        }


        if(!$entityName) return new Response("");//mby change to error?

        $em = $this->getDoctrine()->getManager();

        //get node version
        /** @var NodeVersion $nodeVersion */
        $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
            ->findOneBy(['refEntityName' => $entityName, 'refId' => $page->getId(), 'type' => 'public']);

        if(!$nodeVersion) return new Response("");//mby change to error?

        //get node
        $node = $nodeVersion->getNodeTranslation()->getNode();

        $subscribeForm = new SubscribeForm();
        $subscribeForm->setNode($node->getId());

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
}
