<?php

use rdx\http\HTTP;

function html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function get_url( $path, $query = [] ) {
	if ( strpos($path, '://') === false ) {
		$scheme = @$_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
		$host = $_SERVER['HTTP_HOST'];
		$url = "$scheme$host/";
		$url .= $path ? $path : basename($_SERVER['SCRIPT_NAME']);
	}
	else {
		$url = $path;
	}
	if ( $query ) {
		$url .= '?' . http_build_query($query);
	}
	return $url;
}

function do_redirect( $path, $query = array() ) {
	$url = get_url($path, $query);
	header('Location: ' . $url);
	exit;
}

function spotify_get( $uri, array $query = [] ) {
	$url = 'https://api.spotify.com/' . $uri;
	$query = $query ? '?' . http_build_query($query) : '';
	return SHTTP::create($url . $query, [
		'method' => 'get',
		'headers' => [
			'Authorization: Bearer ' . SPOTIFY_ACCESS_TOKEN,
		],
	])->request();
}

function spotify_put( $uri, array $data ) {
	$url = strpos($uri, '://') == false ? 'https://api.spotify.com/' . $uri : $uri;
	return SHTTP::create($url, [
		'method' => 'put',
		'headers' => [
			'Authorization: Bearer ' . SPOTIFY_ACCESS_TOKEN,
			'Content-type: application/json',
		],
		'data' => json_encode($data),
	])->request();
}

function spotify_get_playlists() {
	$total = -1;
	$playlists = [];
	while ( $total == -1 || count($playlists) < $total ) {
		$response = spotify_get('v1/me/playlists', ['offset' => count($playlists), 'limit' => 50]);
		$data = $response->getResponse();
		if ( !isset($data['items']) ) {
			throw new Exception($response->getBody());
		}
		$total = $data['total'];
		foreach ($data['items'] as $playlist) {
			$playlists[ $playlist['id'] ] = $playlist;
		}
	}

	return $playlists;
}
