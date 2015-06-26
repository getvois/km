<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Command\CreateUserCommand;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Sandbox\WebsiteBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Input\ArrayInput;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints\Email;

class UserController extends Controller
{
    /**
     * @return array
     *
     * @Template()
     */
    public function loginFormAction()
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
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //check password

            $encoder = $this->get('security.encoder_factory')->getEncoder($user);

            if (($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))) {
                // User + password match
                $ip = $request->getClientIp();
                $user->setIp($ip);
                $em->persist($user);
                $em->flush();

                // Here, "main" is the name of the firewall in your security.yml
                $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
                /** @noinspection YamlDeprecatedClasses */
                $this->get("security.context")->setToken($token);

                // Fire the login event
                // Logging the user in above the way we do it doesn't do this automatically
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                // maybe redirect out here
                $this->get('session')->getFlashBag()->add('info', 'Logged in successfully');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl("_slug", ["url" => "club"]));

            } else {
                // Password bad
                $this->get('session')->getFlashBag()->add('error', 'Invalid username or password');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }
        }

        /** @noinspection Symfony2PhpRouteMissingInspection */
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
        /** @noinspection YamlDeprecatedClasses */
        $this->get('security.context')->setToken($token);
        $this->get('request')->getSession()->invalidate();

        /** @noinspection Symfony2PhpRouteMissingInspection */
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
        /** @noinspection YamlDeprecatedClasses */
        /** @noinspection PhpDeprecationInspection */
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
        /** @noinspection PhpUndefinedClassInspection */
        $input = new ArrayInput(array(
            'command'       => 'fos:user:create',
            'username' => $email,
            'email' => $email,
            'password' => $password,
            '--super-admin' => false
        ));
        $input->setInteractive(false);
        /** @noinspection PhpUndefinedClassInspection */
        $output = new NullOutput();
        $resultCode = $command->run($input, $output);

        if($resultCode === 0){
            //get new user
            /** @var User $user */
            $user = $em->getRepository('SandboxWebsiteBundle:User')
                ->findOneBy(['email' => $email]);

            $user->setName($name);
            $user->setHost($request->getHost());

            $hash = md5(microtime() . $user->getEmail());
            $user->setHash($hash);

            $host = $this->get('hosthelper')->getHost();
            if($host){
                $user->setLang($host->getLang());
            }

            $ip = $request->getClientIp();
            $user->setIp($ip);

            $em->persist($user);
            $em->flush();

            //log in user
            // Here, "main" is the name of the firewall in your security.yml
            $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
            /** @noinspection YamlDeprecatedClasses */
            $this->get("security.context")->setToken($token);

            // Fire the login event
            // Logging the user in above the way we do it doesn't do this automatically
            $event = new InteractiveLoginEvent($this->get('request'), $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            //send email with password
            $message = $this->get('templating')->render('@SandboxWebsite/Email/registration.html.twig',
                [
                    'email' => $email,
                    'password' => $password,
                    'hash' => $hash,
                    'baseurl' => $request->getSchemeAndHttpHost(),
                ]);
            $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: info@'.$request->getHost() . "\r\n";
            mail($email, 'Registration', $message, $headers);

            return new JsonResponse(['status' => 'ok', 'msg' => 'Registered successfully<br>Check your email for more details']);
        }else{
            return new JsonResponse(['status' => 'error', 'msg' => 'Error occurred while creating user']);
        }
    }

    /**
     * @Route("/activate/{hash}")
     * @param $hash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($hash)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('SandboxWebsiteBundle:User')
            ->findOneBy(['hash' => $hash]);
        if(!$user)
            throw new NotFoundHttpException('User not found');

        //log in user
        // Here, "main" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
        /** @noinspection YamlDeprecatedClasses */
        $this->get("security.context")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($this->get('request'), $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        /** @noinspection Symfony2PhpRouteMissingInspection */
        return $this->redirect($this->generateUrl("_slug", ['url' => 'club']));
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
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //check email
            $emailConstraint = new Email();
            /** @noinspection YamlDeprecatedClasses */
            /** @noinspection PhpDeprecationInspection */
            $errorList = $this->get('validator')->validateValue(
                $email,
                $emailConstraint
            );

            if (count($errorList) > 0) {
                $this->get('session')->getFlashBag()->add('error', 'Incorrect email');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            $password = md5(microtime() . md5($email));
            //check if email exists
            /** @var User $user */
            $user = $em->getRepository('SandboxWebsiteBundle:User')
                ->findOneBy(['email' => $email]);

            if($user){
                $this->get('session')->getFlashBag()->add('error', 'Email already exist');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            //register user
            $application = new Application($this->get('kernel'));
            $command = new CreateUserCommand();
            $command->setApplication($application);
            $command->setContainer($this->container);
            /** @noinspection PhpUndefinedClassInspection */
            $input = new ArrayInput(array(
                'command'       => 'fos:user:create',
                'username' => $email,
                'email' => $email,
                'password' => $password,
                '--super-admin' => false
            ));
            $input->setInteractive(false);
            /** @noinspection PhpUndefinedClassInspection */
            $output = new NullOutput();
            $resultCode = $command->run($input, $output);

            if($resultCode === 0){
                //get new user
                /** @var User $user */
                $user = $em->getRepository('SandboxWebsiteBundle:User')
                    ->findOneBy(['email' => $email]);

                $user->setName($name);
                $hash = md5(microtime() . $user->getEmail());
                $user->setHash($hash);
                $user->setHost($request->getHost());
                $host = $this->get('hosthelper')->getHost();
                if($host){
                    $user->setLang($host->getLang());
                }

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('info', 'Registered successfully');

                //send email with password
                $message = $this->get('templating')->render('@SandboxWebsite/Email/registration.html.twig',
                    [
                        'email' => $email,
                        'password' => $password,
                        'hash' => $hash,
                        'baseurl' => $request->getSchemeAndHttpHost(),
                    ]);
                $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= 'From: info@'.$request->getHost() . "\r\n";
                mail($email, 'Registration', $message, $headers);

            }else{
                $this->get('session')->getFlashBag()->add('error', 'Error occurred while creating user');
            }

        }
        /** @noinspection Symfony2PhpRouteMissingInspection */
        return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
    }


    /**
     * @Route("/vk-login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginVkAction(Request $request)
    {
        if($request->query->has('code')){

            $code = $request->query->get('code');
            $host = $this->get('hosthelper')->getHost();
            if(!$host || !$host->getVkAppId() || !$host->getVkAppSecret()){
                $this->get('session')->getFlashBag()->add('error', 'Invalid host or app data not set');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
            }

            $url = "https://oauth.vk.com/access_token?client_id={$host->getVkAppId()}&client_secret={$host->getVkAppSecret()}&code=$code&redirect_uri={$request->getSchemeAndHttpHost()}/vk-login";
            $content = @file_get_contents($url);
            if($content){
                $data = json_decode($content);
                $access_token = $data->access_token;
                $user_id = $data->user_id;
                $email = $data->email;
                $name = "";

                $content = @file_get_contents("https://api.vk.com/method/users.get?user_id=$user_id&v=5.29&access_token=$access_token");
                if($content){
                    $userData = json_decode($content);
                    $name = $userData->response[0]->first_name." ".$userData->response[0]->last_name;
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                //get user
                /** @var User $user */
                $user = $em->getRepository('SandboxWebsiteBundle:User')
                    ->findOneBy(['email' => $email]);

                if(!$user){
                    //register new user
                    //register user
                    $password = md5(microtime() . md5($email));
                    $application = new Application($this->get('kernel'));
                    $command = new CreateUserCommand();
                    $command->setApplication($application);
                    $command->setContainer($this->container);
                    /** @noinspection PhpUndefinedClassInspection */
                    $input = new ArrayInput(array(
                        'command'       => 'fos:user:create',
                        'username' => $email,
                        'email' => $email,
                        'password' => $password,
                        '--super-admin' => false
                    ));
                    $input->setInteractive(false);
                    /** @noinspection PhpUndefinedClassInspection */
                    $output = new NullOutput();
                    $resultCode = $command->run($input, $output);

                    if($resultCode === 0){
                        //get new user
                        /** @var User $user */
                        $user = $em->getRepository('SandboxWebsiteBundle:User')
                            ->findOneBy(['email' => $email]);

                        $user->setName($name);
                        $user->setHost($request->getHost());
                        $user->setLang($host->getLang());

                        $em->persist($user);
                        $em->flush();

                    }else{
                        $this->get('session')->getFlashBag()->add('error', 'Error occurred while creating user');
                        /** @noinspection Symfony2PhpRouteMissingInspection */
                        return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
                    }
                }

                $ip = $request->getClientIp();
                $user->setIp($ip);
                $em->persist($user);
                $em->flush();

                // User + password match
                // Here, "main" is the name of the firewall in your security.yml
                $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
                /** @noinspection YamlDeprecatedClasses */
                $this->get("security.context")->setToken($token);

                // Fire the login event
                // Logging the user in above the way we do it doesn't do this automatically
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                // maybe redirect out here
                $this->get('session')->getFlashBag()->add('info', 'Logged in successfully');
                /** @noinspection Symfony2PhpRouteMissingInspection */
                return $this->redirect($this->generateUrl("_slug", ["url" => "club"]));
            }



        }

        /** @noinspection Symfony2PhpRouteMissingInspection */
        return $this->redirect($this->generateUrl('_slug', ["url" => ""]));
    }


    /**
     * @Route("/login-fb/{token}")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     * @throws \Exception
     */
    public function loginFBAction(Request $request, $token)
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
            /** @noinspection PhpUndefinedClassInspection */
            $input = new ArrayInput(array(
                'command'       => 'fos:user:create',
                'username' => $email,
                'email' => $email,
                'password' => $password,
                '--super-admin' => false
            ));
            $input->setInteractive(false);
            /** @noinspection PhpUndefinedClassInspection */
            $output = new NullOutput();
            $resultCode = $command->run($input, $output);

            if($resultCode === 0){
                //get new user
                /** @var User $user */
                $user = $em->getRepository('SandboxWebsiteBundle:User')
                    ->findOneBy(['email' => $email]);

                $user->setName($name);
                $user->setHost($request->getHost());
                $host = $this->get('hosthelper')->getHost();
                if($host){
                    $user->setLang($host->getLang());
                }
                $em->persist($user);
                $em->flush();

            }else{
                return new JsonResponse(['status' => 'error', 'msg' => 'Error occurred while creating user']);
            }
        }

        $ip = $request->getClientIp();
        $user->setIp($ip);
        $em->persist($user);
        $em->flush();

        //log in user
        // Here, "main" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
        /** @noinspection YamlDeprecatedClasses */
        $this->get("security.context")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($this->get('request'), $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        return new JsonResponse(['status' => 'ok']);
    }


    /**
     * @Route("/user-edit/")
     * @param Request $request
     * @return JsonResponse
     */
    public function userEditAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user)
            return new JsonResponse(['status' => 'error', 'msg' => 'user not found']);

        if($request->query->has('name')){
            $name = $request->query->get('name');
            if($name){
                $user->setName($name);
            }
        }
        if($request->query->has('country')){
            $country = $request->query->get('country');
            if($country){
                $user->setCountry($country);
            }
        }
        if($request->query->has('city')){
            $city = $request->query->get('city');
            if($city){
                $user->setCity($city);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'ok', 'msg' => 'data saved']);
    }

    /**
     * @Route("/user-password/")
     * @param Request $request
     * @return JsonResponse
     */
    public function userEditPasswordAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user)
            return new JsonResponse(['status' => 'error', 'msg' => 'user not found']);

        if(!$request->query->has('oldpassword') || !$request->query->get('oldpassword')){
            return new JsonResponse(['status' => 'error', 'msg' => 'current password is empty']);
        }

        //check current password
        $password = $request->query->get('oldpassword');
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        if (!($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))) {
            return new JsonResponse(['status' => 'error', 'msg' => 'invalid password']);
        }


        if(!$request->query->has('newpassword') || !$request->query->get('newpassword')
        || !$request->query->has('renewpassword') || !$request->query->get('renewpassword')
        ){
            return new JsonResponse(['status' => 'error', 'msg' => 'new password is empty']);
        }

        if($request->query->get('newpassword') != $request->query->get('renewpassword')){
            return new JsonResponse(['status' => 'error', 'msg' => 'passwords does not match']);
        }

        $password = $request->query->get('newpassword');

        if(strlen($password) < 8){
            return new JsonResponse(['status' => 'error', 'msg' => 'passwords should be 8 or more characters long']);
        }

        $password = $encoder->encodePassword($password, $user->getSalt());

        $user->setPassword($password);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'ok', 'msg' => 'password changed']);
    }

    /**
     * @Route("/reset-password/")
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user)
            return new JsonResponse(['status' => 'error', 'msg' => 'user not found']);

        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $rawPassword = str_split(md5(rand(0,99999)), 8)[0];
        $password = $encoder->encodePassword($rawPassword, $user->getSalt());

        $user->setPassword($password);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $message = $this->get('templating')->render('@SandboxWebsite/Email/passwordReset.html.twig', ['rawPassword' => $rawPassword]);
        //$message = "Your new password: " . $rawPassword;
        $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: info@'.$request->getHost() . "\r\n";
        mail($user->getEmail(), 'Password reset', $message, $headers);

        return new JsonResponse(['status' => 'ok', 'msg' => 'Password is send to your email']);

    }


    /**
     * @Route("/user-import/")
     * @return JsonResponse
     */
    public function usersFromCsvAction()
    {
        $emails = [];
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        foreach ($emails as $email) {
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
                /** @noinspection PhpUndefinedClassInspection */
                $input = new ArrayInput(array(
                    'command'       => 'fos:user:create',
                    'username' => $email,
                    'email' => $email,
                    'password' => $password,
                    '--super-admin' => false
                ));
                $input->setInteractive(false);
                /** @noinspection PhpUndefinedClassInspection */
                $output = new NullOutput();
                $resultCode = $command->run($input, $output);

                if($resultCode === 0){
                    //get new user
                    /** @var User $user */
                    $user = $em->getRepository('SandboxWebsiteBundle:User')
                        ->findOneBy(['email' => $email]);

                    //$user->setName($name);
                    $em->persist($user);
                    $em->flush();

                }else{
                    return new JsonResponse(['status' => 'error', 'msg' => 'Error occurred while creating user']);
                }
            }
        }

        return new JsonResponse(['status' => 'ok', 'msg' => 'done']);

    }


    /**
     * @Route("/password-reset/{email}")
     * @param Request $request
     * @param $email
     * @return JsonResponse
     */
    public function resetPasswordEmailAction(Request $request, $email)
    {

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('SandboxWebsiteBundle:User')
            ->findOneBy(['email' => $email]);
        if(!$user)
            return new JsonResponse(['status' => 'error', 'msg' => 'user not found']);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $rawPassword = str_split(md5(rand(0,99999)), 10)[0];
        $password = $encoder->encodePassword($rawPassword, $user->getSalt());

        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

        $message = $this->get('templating')->render('@SandboxWebsite/Email/passwordReset.html.twig', ['rawPassword' => $rawPassword]);
        //$message = "Your new password: " . $rawPassword;
        $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: info@'.$request->getHost() . "\r\n";
        mail($user->getEmail(), 'Password reset', $message, $headers);

        return new JsonResponse(['status' => 'ok', 'msg' => 'Password is send to your email']);

    }

    /**
     * @Route("/password-reset/")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function resetPasAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Node[] $node */
        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodesByInternalName('homepage', $request->getLocale());

        $page = $node[0]->getNodeTranslation($request->getLocale())->getRef($em);

        //for top and bottom menu
        /** @noinspection YamlDeprecatedClasses */
        $securityContext = $this->get('security.context');
        $aclHelper      = $this->container->get('kunstmaan_admin.acl.helper');
        $nodeMenu       = new NodeMenu($em, $securityContext, $aclHelper, $request->getLocale(), $node[0], PermissionMap::PERMISSION_VIEW);
        //
        return ['nodemenu' => $nodeMenu, 'page' => $page];
    }


    /**
     * @Route("/user-subscriptions-edit/")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveSubscriptions(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user)
            return new JsonResponse(['status' => 'error', 'msg' => 'user not found']);

            $flightOffers = $request->query->get('flightOffers', false);
            if($flightOffers == 'on') $flightOffers = true;
            $user->setFlightOffers($flightOffers);

            $localOffers = $request->query->get('localOffers', false);
            if($localOffers == 'on') $localOffers = true;
            $user->setLocalOffers($localOffers);

            $internationalOffers = $request->query->get('internationalOffers', false);
            if($internationalOffers == 'on') $internationalOffers = true;
            $user->setInternationalOffers($internationalOffers);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'ok', 'msg' => 'data saved']);
    }
}