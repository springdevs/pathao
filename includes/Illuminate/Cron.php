<?php

namespace SpringDevs\Pathao\Illuminate;

use GuzzleHttp\Exception\RequestException;

/**
 * Class Cron
 *
 * @package SpringDevs\Pathao\Illuminate
 */
class Cron
{

    public function __construct()
    {
        add_action('pathao_refresh_token_cron', array($this, 'update_token'));
    }

    public function update_token()
    {
        $client_id = get_option('pathao_client_id');
        $client_secret = get_option('pathao_client_secret');
        $refresh_token = get_option('pathao_refresh_token');

        if (!$client_id || !$client_secret || !$refresh_token) {
            return;
        }

        $client = new \GuzzleHttp\Client();
        $base_url = get_pathao_base_url();
        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ];

        try {
            $res = $client->request('POST', $base_url . 'aladdin/api/v1/issue-token', [
                'form_params' => $data
            ]);
            $data = json_decode($res->getBody()->getContents());
            update_option('pathao_access_token', $data->access_token);
            update_option('pathao_refresh_token', $data->refresh_token);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $res = $e->getResponse();
                $errors = json_decode($res->getBody()->getContents());
            }
        }
    }
}
