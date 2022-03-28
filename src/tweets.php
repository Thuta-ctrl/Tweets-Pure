<?php

namespace ThutaYarMoe\PureTweets;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

class Tweets {
    private $client;
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function get_tweets($users) {
        $tweets = [];
        foreach ($users as $key => $value) {
            try {
                $response = $this->client->request('GET', 'https://api.twitter.com/2/users/'.$value['id'].'/tweets', 
                [   
                    'query' => [
                        'tweet.fields' => 'created_at,author_id,lang,source,public_metrics,context_annotations,entities',
                        'max_results' => 5,
                        'user.fields' => 'created_at,name,profile_image_url',
                        'expansions' => 'author_id,attachments.media_keys',
                        'media.fields' => 'preview_image_url,type,url'
                    ],
                    'headers' => [
                        'Content-type' => 'application/json',
                        'Authorization' => 'Bearer ' . $value['token']
                    ]
                ]);
            } catch (ClientException $e) {
                return Psr7\Message::toString($e->getResponse());
            }

            $body = $response->getBody();
            array_push($tweets, json_decode($body, true));
            
            $collection = [];
            foreach ($tweets as $value) {
                array_push($collection, $this->prepare_tweets($value));
            }

            $data = array_merge(...$collection);

            usort($data, function($a, $b){
                return $b['created_at'] <=> $a['created_at'];
            });

        }
        return $data;
    }

    public function prepare_tweets($tweets) {
        $main = $tweets['data'];
         foreach ($main as $key => $value) {
            $main[$key]['link'] = 'https://twitter.com/spiceworksmm/status/'.$value['id'];
            $main[$key]['user'] = $tweets['includes']['users'][0];
            $main[$key]['images'] = !empty($value['attachments']) ? $this->get_images($value['attachments']['media_keys'], $tweets['includes']['media']): null;
        }

        return $main;
    }

    public function get_images($keys, $images) {
        $img = [];
        foreach ($keys as $key => $value) {
          $image = $images[array_search($value, array_column($images, 'media_key'))];
          if ($image['type'] == 'photo') {
            array_push($img, $image['url']);
          } else if ($image['type'] == 'video') {
            array_push($img, $image['preview_image_url']);
          }
        }
        return $img;
    }
}