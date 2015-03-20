<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;

class VkHelper {

    private $access_token = 'c837a4c8d681ac8f7e0c92f3ba59ac87d437451b151a4674d7ac25ec5450d285eb0951b03d0c0e03ea239';
    private $group_id = '-53828652';

    public function getAuthLink($APP_ID)
    {

        //$APP_ID = '4832944';
        $PERMISSIONS = 'wall,groups,offline';
        $REDIRECT_URI = 'https://oauth.vk.com/blank.html';
        $DISPLAY = 'page';
        $API_VERSION = '5.29';

        $auth = "https://oauth.vk.com/authorize?
        client_id=$APP_ID&
        scope=$PERMISSIONS&
        redirect_uri=$REDIRECT_URI&
        display=$DISPLAY&
        v=$API_VERSION&
        response_type=token";

        return $auth;
    }


    public function postOnWall($object, EntityManager $em, Host $host)
    {
        if(!$host->getVkGroupId() || !$host->getVkAccessToken())
            return ;

        $access_token= $host->getVkAccessToken();

        $owner_id = $host->getVkGroupId();
        //make sure that id is negative number
        if($owner_id > 0) $owner_id *= -1;

        //post as group
        $from_group = 1;

        $url = '';
        if($object instanceof NewsPage || $object instanceof ArticlePage){
            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($object);

            if(!$node) return;
            $translation = $node->getNodeTranslation($host->getLang());
            if(!$translation) return;

            $page = $translation->getRef($em);

            $url = "http://" . $host->getName() . '/';
            if($host->getMultiLanguage()){
                $url .= $host->getLocale() . "/";
            }

            $url .= $translation->getFullSlug();
        }

        if(!$url) return ;

        //$attachments = 'http://rannapuhkus.ee/blog/autohuvilised-roomustage-3-valjapaistvat-sundmust-madeiral';
        $attachments = $url;

        $api = "https://api.vk.com/method/wall.post?owner_id=$owner_id&from_group=$from_group&attachments=$attachments&access_token=$access_token";

        file_get_contents($api);
    }
}