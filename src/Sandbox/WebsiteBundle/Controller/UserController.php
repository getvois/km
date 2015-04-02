<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Command\CreateUserCommand;
use Sandbox\WebsiteBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints\Email;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function loginFormAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/login", name="login_action")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if($request->getMethod() == "POST"){

            $email = $request->request->get('email');
            $password = $request->request->get('password');


            //get user
            /** @var User $user */
            $user = $em->getRepository('SandboxWebsiteBundle:User')
                ->findOneBy(['email' => $email]);

            if(!$user){
                $this->get('session')->getFlashBag()->add('error', 'Invalid username or password');
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //check password

            $encoder = $this->get('security.encoder_factory')->getEncoder($user);

            if (($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))) {
                // User + password match
                // Here, "main" is the name of the firewall in your security.yml
                $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
                $this->get("security.context")->setToken($token);

                // Fire the login event
                // Logging the user in above the way we do it doesn't do this automatically
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                // maybe redirect out here
                $this->get('session')->getFlashBag()->add('info', 'Logged in successfully');
                return $this->redirect($this->generateUrl("_slug", ["url" => ""]));

            } else {
                // Password bad
                $this->get('session')->getFlashBag()->add('error', 'Invalid username or password');
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }
        }

        return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
    }

    /**
     * @Route("/logout", name="logout_action")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $token = new AnonymousToken($providerKey, 'anon.');
        $this->get('security.context')->setToken($token);
        $this->get('request')->getSession()->invalidate();

        return $this->redirect($this->generateUrl('_slug', ['url' => '']));
    }

    /**
     * @Route("/register-ajax/", name="register_ajax")
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAjaxAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $name = $request->query->get('name');
        $email = $request->query->get('email');

        if(!$email){
            return new JsonResponse(['status' => 'error', 'msg' => 'Empty email']);
        }

        //check email
        $emailConstraint = new Email();
        $errorList = $this->get('validator')->validateValue(
            $email,
            $emailConstraint
        );

        if (count($errorList) > 0) {
            return new JsonResponse(['status' => 'error', 'msg' => 'Incorrect email']);
        }


        $password = md5(microtime() . md5($email));
        //check if email exists
        /** @var User $user */
        $user = $em->getRepository('SandboxWebsiteBundle:User')
            ->findOneBy(['email' => $email]);

        if($user){
            return new JsonResponse(['status' => 'error', 'msg' => 'Email already exist']);
        }

        //register user
        $application = new Application($this->get('kernel'));
        $command = new CreateUserCommand();
        $command->setApplication($application);
        $command->setContainer($this->container);
        $input = new ArrayInput(array(
            'command'       => 'fos:user:create',
            'username' => $email,
            'email' => $email,
            'password' => $password,
            '--super-admin' => false
        ));
        $input->setInteractive(false);
        $output = new NullOutput();
        $resultCode = $command->run($input, $output);

        if($resultCode === 0){
            //get new user
            /** @var User $user */
            $user = $em->getRepository('SandboxWebsiteBundle:User')
                ->findOneBy(['email' => $email]);

            $user->setName($name);
            $em->persist($user);
            $em->flush();

            //send email with password
            $message = "Thank you for joining our Club\nUser email:{$user->getEmail()}\nPassword: $password";

            //log in user
            // Here, "main" is the name of the firewall in your security.yml
            $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
            $this->get("security.context")->setToken($token);

            // Fire the login event
            // Logging the user in above the way we do it doesn't do this automatically
            $event = new InteractiveLoginEvent($this->get('request'), $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            mail($email, 'Registration', $message, 'From: info@'.$request->getHost());
            return new JsonResponse(['status' => 'ok', 'msg' => 'Registered successfully<br>Check your email for more details']);
        }else{
            return new JsonResponse(['status' => 'error', 'msg' => 'Error occurred while creating user']);
        }
    }

    /**
     * @Route("/register", name="register_action")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function registerAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if($request->getMethod() == "POST") {

            $name = $request->request->get('name');
            $email = $request->request->get('email');

            if(!$email){
                $this->get('session')->getFlashBag()->add('error', 'Empty email');
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //check email
            $emailConstraint = new Email();
            $errorList = $this->get('validator')->validateValue(
                $email,
                $emailConstraint
            );

            if (count($errorList) > 0) {
                $this->get('session')->getFlashBag()->add('error', 'Incorrect email');
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            $password = md5(microtime() . md5($email));
            //check if email exists
            /** @var User $user */
            $user = $em->getRepository('SandboxWebsiteBundle:User')
                ->findOneBy(['email' => $email]);

            if($user){
                $this->get('session')->getFlashBag()->add('error', 'Email already exist');
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //register user
            $application = new Application($this->get('kernel'));
            $command = new CreateUserCommand();
            $command->setApplication($application);
            $command->setContainer($this->container);
            $input = new ArrayInput(array(
                'command'       => 'fos:user:create',
                'username' => $email,
                'email' => $email,
                'password' => $password,
                '--super-admin' => false
            ));
            $input->setInteractive(false);
            $output = new NullOutput();
            $resultCode = $command->run($input, $output);

            if($resultCode === 0){
                //get new user
                /** @var User $user */
                $user = $em->getRepository('SandboxWebsiteBundle:User')
                    ->findOneBy(['email' => $email]);

                $user->setName($name);
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('info', 'Registered successfully');

                //send email with password
                $message = "Your password: $password";
                mail($email, 'Registration', $message, 'From: info@'.$request->getHost());
            }else{
                $this->get('session')->getFlashBag()->add('error', 'Error occurred while creating user');
            }

        }
            return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
    }


    /**
     * @Route("/login-fb/{token}")
     * @param $token
     * @return JsonResponse
     */
    public function loginFBAction($token)
    {
        //get email by token
        $content = file_get_contents("https://graph.facebook.com/v2.3/me?access_token=$token");
        if(!$content)
            return new JsonResponse(['status' => 'error', 'msg' => 'Error while fetching data']);

        $data = json_decode($content);

        //if some fb error(invalid token etc...)
        if(property_exists($data, 'error')){
            return new JsonResponse(['status' => 'error', 'msg' => $data->error->message]);
        }

        $name = $data->name;
        $email = $data->email;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //check if email exists
        /** @var User $user */
        $user = $em->getRepository('SandboxWebsiteBundle:User')
            ->findOneBy(['email' => $email]);

        if(!$user){
            //register user
            $password = md5(microtime() . md5($email));
            $application = new Application($this->get('kernel'));
            $command = new CreateUserCommand();
            $command->setApplication($application);
            $command->setContainer($this->container);
            $input = new ArrayInput(array(
                'command'       => 'fos:user:create',
                'username' => $email,
                'email' => $email,
                'password' => $password,
                '--super-admin' => false
            ));
            $input->setInteractive(false);
            $output = new NullOutput();
            $resultCode = $command->run($input, $output);

            if($resultCode === 0){
                //get new user
                /** @var User $user */
                $user = $em->getRepository('SandboxWebsiteBundle:User')
                    ->findOneBy(['email' => $email]);

                $user->setName($name);
                $em->persist($user);
                $em->flush();

            }else{
                return new JsonResponse(['status' => 'error', 'msg' => 'Error occurred while creating user']);
            }
        }

        //log in user
        // Here, "main" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
        $this->get("security.context")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($this->get('request'), $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        return new JsonResponse(['status' => 'ok']);
    }
}