<?php

use rdx\http\HTTP;

if ( php_sapi_name() == 'cli' ) {
	define('SPOTIFY_ACCESS_TOKEN', trim(file_get_contents('.access_token')));
	if ( !SPOTIFY_ACCESS_TOKEN ) {
		echo "Make access token via web.\n";
		exit(1);
	}

	return;
}

if ( isset($_COOKIE['spotify_token']) ) {
	define('SPOTIFY_ACCESS_TOKEN', $_COOKIE['spotify_token']);
	return;
}

$redirectUri = get_url(null);

if ( isset($_GET['code']) ) {
	$rsp = spotify_http(['headers' => []])->post('https://accounts.spotify.com/api/token', [
		'form_params' => [
			'grant_type' => 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri' => $redirectUri,
		],
		'auth' => [SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET],
	]);
	$json = (string) $rsp->getBody();
	$data = json_decode($json, true);

	if ( isset($data['access_token']) ) {
		setcookie('spotify_token', $data['access_token'], strtotime('+1 year'));
		file_put_contents('.access_token', $data['access_token']);
		return do_redirect(null);
	}

	echo '<p><a href="' . $redirectUri . '">Retry</a></p>';
	echo $response;
	exit;
}

if ( !empty($_GET) ) {
	header('Content-type: text/plain; charset=utf-8');
	print_r($_GET);
	exit;
}

do_redirect('https://accounts.spotify.com/authorize', [
	'response_type' => 'code',
	'client_id' => SPOTIFY_CLIENT_ID,
	'redirect_uri' => $redirectUri,
	'scope' => implode(' ', SPOTIFY_SCOPES),
]);
