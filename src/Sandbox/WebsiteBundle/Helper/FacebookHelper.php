<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Symfony\Component\HttpFoundation\Request;

class FacebookHelper {

    private $app_id = '1577226712516009';
    private $app_secret = '0360eae31e886d85e8a4d2d9ea4bc448';
    private $group_id = '424808657694546';
    private $page_access_token = 'CAAWaerXuBakBAIj5gwS9Wle98HhignCfpfB6AZAD7HNcQKCovQ4r88vFlazotwncbURh38uu4CDlrK4lZC71LYlmdpZCU0RHxlgJpnSwplN59ZAo7Vtq7ZAwFqDXCJCOOQlt4EegaGlYQZCgrYdHa1ZCOpIVBZB6skppM3cKCZBdgGQz3roa66aGu';

    public function postOnWall($object, EntityManager $em, Host $host)
    {
        /*

Go to the Graph API Explorer
https://developers.facebook.com/tools/explorer
Choose your app from the dropdown menu
Click "Get Access Token"
Choose the manage_pages permission
Now access the me/accounts connection and copy your page's access_token
Click on your page's id
Add the page's access_token to the GET fields
Call the connection you want (e.g.: PAGE_ID/events)


PERMANENT KEY

    Make sure you are the admin of the FB page you wish to pull info from
    Create a FB App (should be with the same user account that is the page admin)
    Head over to the Facebook Graph API Explorer
    On the top right, select the FB App you created from the "Application" drop down list
    Click "Get Access Token"
    Make sure you add the manage_pages permission
    Convert this short-lived access token into a long-lived one by making this Graph API call:
        https://graph.facebook.com/oauth/access_token?client_id=<your FB App ID >&client_secret=<your FB App secret>&grant_type=fb_exchange_token&fb_exchange_token=<your short-lived access token>
    Grab the new long-lived access token returned back
    Make a Graph API call to see your accounts using the new long-lived access token:
        https://graph.facebook.com/me/accounts?access_token=<your long-lived access token>
    Grab the access_token for the page you'll be pulling info from
    Lint the token to see that it is set to Expires: Never!


 */

        if(    !$host->getAppId()
            || !$host->getAppSecret()
            || !$host->getGroupId()
            || !$host->getPageAccessToken()){
            return;
        }

        $this->app_id = $host->getAppId();
        $this->app_secret = $host->getAppSecret();
        $this->group_id = $host->getGroupId();
        $this->page_access_token = $host->getPageAccessToken();

        FacebookSession::setDefaultApplication($this->app_id, $this->app_secret);

        // If you're making app-level requests:
        $session = FacebookSession::newAppSession();

        // To validate the session:
        try {
            $session->validate();

            $message = '';

            $url = '';
            if($object instanceof NewsPage || $object instanceof ArticlePage){
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($object);

                if(!$node) return;
                $translation = $node->getNodeTranslation($host->getLang());
                if(!$translation) return;

                $page = $translation->getRef($em);

                $url = "http://" . $host->getName() . '/' . $translation->getFullSlug();

                //$url = 'http://rannapuhkus.ee/blog/autohuvilised-roomustage-3-valjapaistvat-sundmust-madeiral';

                $message = $page->getTitle();
                $message .= "\n" . $url;
            }

            if(!$message) return;

//            $request = new FacebookRequest(
//                $session,
//                'GET',
//                '/',
//                array (
//                    'id' => $url,
//                    'access_token' => $this->page_access_token,
//                )
//            );
//            $response = $request->execute();
//            $graphObject = $response->getGraphObject();
//
//            var_dump($graphObject);
            $request = new FacebookRequest(
                $session,
                'POST',
                '/'.$this->group_id.'/feed',
                array (
                    'link' => $url,
                    'access_token' => $this->page_access_token,
                )
            );
            $request->execute();

        } catch (FacebookRequestException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
            echo $ex->getMessage();
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
            echo $ex->getMessage();
        }

    }

}