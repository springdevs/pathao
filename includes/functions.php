<?php

/**
 * All our plugins custom functions.
 *
 * @since 1.0.0
 */

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
  return get_option('pathao_sandbox_mode') ? "https://api-hermes.pathao.com/" :  "https://hermes-api.p-stageenv.xyz/" ;
}

function getData(String $endpoint)
{
  $base_url = get_pathao_base_url();
  $access_token = get_option("pathao_access_token");

  if (!$access_token) return (object)[
    'type' => 'failed',
    'error' => 'Please generate access token to use pathao plugin !!'
  ];

  $res = wp_remote_get($base_url . $endpoint, [
    'headers' => [
      'Authorization' => 'Bearer ' . $access_token,
      'Accept' => 'application/json',
    ],
  ]);

  $data = json_decode(wp_remote_retrieve_body($res));
  $res_code = wp_remote_retrieve_response_code($res);

  if ($res_code == 200) {
    return $data;
  }

  return (object)[
    "type" => "failed"
  ];
}
