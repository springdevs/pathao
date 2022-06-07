<?php

/**
 * All our plugins custom functions.
 *
 * @since 1.0.0
 */

use GuzzleHttp\Exception\RequestException;

/**
 * Get filename extension.
 *
 * @param string $file_name File name.
 *
 * @since 1.0.0
 *
 * @return false|string
 */
function sdevs_get_pathao_get_extension($file_name)
{
  $n = strrpos($file_name, '.');

  return (false === $n) ? '' : substr($file_name, $n + 1);
}

function get_pathao_base_url(): string
{
  return get_option('pathao_sandbox_mode') ? "https://hermes-api.p-stageenv.xyz/" : "https://api-hermes.pathaointernal.com/";
}

function getData(String $endpoint)
{
  $client = new \GuzzleHttp\Client();
  $base_url = get_pathao_base_url();
  $access_token = get_option("pathao_access_token");

  if (!$access_token) return ['error' => 'Please generate access token to use pathao plugin !!'];

  try {
    $res = $client->request('GET', $base_url . $endpoint, [
      'headers' => [
        'Authorization' => "Bearer {$access_token}"
      ]
    ]);
    return json_decode($res->getBody()->getContents());
  } catch (RequestException $e) {
    if ($e->hasResponse()) {
      $res = $e->getResponse();
      return json_decode($res->getBody()->getContents());
    }
  } catch (\GuzzleHttp\Exception\GuzzleException $e) {
    return ["error" => $e->getMessage()];
  }
}
