<?php
/**
 * @package SJ Social Media Counter
 * @version 1.0.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die ();

abstract class SjSocialMediaCountsHelper
{
    public static function getList(&$params)
    {
        $return = array();
        if ((int)$params->get('display_sfacebook', 0)) {
            $return['count_facebook_like'] = self::getFacebookLike($params);
        }

        if ((int)$params->get('display_stwitter', 0)) {
            $return['count_followers_twitter'] = self::getFollowersTwitter($params);
        }

        if ((int)$params->get('display_slinkedin', 0)) {
            $return['count_followers_linkedin'] = self::getFollowersLinkedin($params);
        }

        if ((int)$params->get('display_svimeo', 0)) {
            $return['count_followers_vimeo'] = self::getFollowersVimeo($params);
        }

        if ((int)$params->get('display_ssoundcloud', 0)) {
            $return['count_followers_soundcloud'] = self::getFollowersSoundCloud($params);
        }

        if ((int)$params->get('display_sdribbble', 0)) {
            $return['count_followers_dribbble'] = self::getFollowersDribbble($params);
        }

        if ((int)$params->get('display_syoutube', 0)) {
            $return['count_subscribers_youtube'] = self::getSubscribersYoutube($params);
        }

        if ((int)$params->get('display_sgplus', 0)) {
            $return['count_followers_gplus'] = self::getFollowersGplus($params);
        }

        if ((int)$params->get('display_sinstagram', 0)) {
            $return['count_followers_instagram'] = self::getFollowersInstagram($params);
        }

        if ((int)$params->get('display_rss', 0)) {
            $return['rss_url'] = $params->get('rss_url', '#');
        }

        return $return;
    }

    // Facebook //
    private static function getFacebookLike1($params)
    {
        $url = $params->get('facebook_url');
        $url = urlencode($url);
        $json_string = @file_get_contents('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls=' . $url);
        $json = json_decode($json_string, true);
        $like_count = isset($json['0']) && $json['0']['like_count'] ? $json['0']['like_count'] : 0;
        return $like_count;
    }
    private static function getFacebookLike($params){
        $id = $params->get('facebook_url');
        $appid = $params->get('appid');
        $appsecret = $params->get('appsecret');
        $json_url ='https://graph.facebook.com/'.$id.'?access_token='.$appid.'|'.$appsecret.'&fields=fan_count';
        $json = @file_get_contents($json_url);
        $json_output = json_decode($json);
        //Extract the likes count from the JSON object
        $like_count = isset($json_output->fan_count) ? $json_output->fan_count : 0;
        return $like_count;
    }

    // Twitter //
    private static function getFollowersTwitter($params)
    {
        if(!class_exists('TwitterOAuth')){

            require_once dirname( __FILE__ ).'/twitteroauth.php';

        }
        $consumerKey = $params->get('consumekey','kGeoSxX3i60oXODNAlOCw');
        $consumerSecret = $params->get('consumersecret','EqvsqEwoeYMMITJ3fSYaVjSuv7w6ORwt5sADuuNYcs');
        $oAuthToken = null;
        $oAuthSecret = null;
        $screenName = $params->get('screenname','smartaddons');
        $Tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
        $foll = $Tweet->get("https://api.twitter.com/1.1/users/lookup.json?screen_name=" . $screenName);
        $items = json_decode($foll);
        
        if(isset($items->errors)) {
            $followers_count = 0; 
        } else {
            $followers_count = isset($items[0]->followers_count)?$items[0]->followers_count:0;     
        }
        
        return $followers_count;

    }

    // Linkedin //
    private static function getFollowersLinkedin($params)
    {
        $url = $params->get('linkedin_url','http://www.linkedin.com/in/smartaddons');
        $url = urlencode($url);
        $json_string = @file_get_contents("http://www.linkedin.com/countserv/count/share?url=$url&format=json");
        $json = json_decode($json_string, true);
        $followers_count = isset($json['fCntPlusOne'])?$json['fCntPlusOne']:0;
        return $followers_count;
    }

    // Vimeo //
    private static function getFollowersVimeo($params)
    {
        $url = $params->get('vimeo_username', 'royalhandmadecustoms');
        $url = urlencode($url);
        $json_string = @file_get_contents('http://vimeo.com/api/v2/' . $url . '/info.json');
        $json = json_decode($json_string, true);
        $followers_count = isset($json['total_contacts']) ? $json['total_contacts'] : 0;
        return $followers_count;
    }

    // SoundCloud //
    private static function getFollowersSoundCloud($params)
    {
        $soundc_un = $params->get('soundc_un', null);
        $soundc_id = $params->get('soundc_id', null);
        $json_string = @file_get_contents('http://api.soundcloud.com/users/' . $soundc_un . '.json?client_id=' . $soundc_id);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['followers_count']) ? $json['followers_count'] : 0;
        return $followers_count;
    }


    // Dirbbble //
    private static function getFollowersDribbble( $params ){
        $dr_token = $params->get('access_token');
        $data_all       = "https://api.dribbble.com/v1/user?access_token=".$dr_token;
        $data_count     = @file_get_contents($data_all);
        $d_data         = json_decode( $data_count, true );         
        $dribbble_count = $d_data['followers_count'];
        return $dribbble_count;
    }

    // Subscribers Youtube //
    private static function getSubscribersYoutube($params)
    {
        global $social_counter_settings;
        $settings = $social_counter_settings;
        $count = 261;                  
        $youtubeUrl = "https://www.googleapis.com/youtube/v3/channels?part=statistics&id=".$params->get('youtube_channel_id')."&fields=items/statistics/subscriberCount&key=".$params->get('google_api_key');
        $response = @file_get_contents($youtubeUrl);         
        $fb = json_decode($response);
        if ( isset( $fb->items[0])) {                
            $count = intval( $fb->items[0]->statistics -> subscriberCount);                    
        }                   
        return $count ;
    }

    // Google Plus //
    private static function getFollowersGplus($params)
    {
        $gplus_id = $params->get('gplus_id');
        $gplus_key = $params->get('gplus_key');
        $json_string = @file_get_contents('https://www.googleapis.com/plus/v1/people/' . $gplus_id . '?key=' . $gplus_key);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['circledByCount']) ? $json['circledByCount'] : 0;
        return $followers_count;
    }

    // Instagram //
    private static function  getFollowersInstagram($params)
    {
        $userID = $params->get('inst_userid', null);
        $access_token = $params->get('inst_access_token', null);
        $json_string = @file_get_contents('https://api.instagram.com/v1/users/' . $userID . '?access_token=' . $access_token);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['data']['counts']['followed_by']) ? $json['data']['counts']['followed_by'] : 0;
        return $followers_count;
    }
}


