<?php

use GuzzleHttp\Client as Guzzle;

$GLOBALS['http_log'] = [];

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

function spotify_http( array $options = [] ) {
	if ( !isset($options['headers']) ) {
		$options['headers'] = [
			'Authorization' => 'Bearer ' . SPOTIFY_ACCESS_TOKEN,
			'Content-type' => 'application/json',
		];
	}
	return new Guzzle($options);
}

function spotify_get( string $uri, array $query = [] ) {
	$url = 'https://api.spotify.com/' . $uri;
	$query = $query ? '?' . http_build_query($query) : '';
	$GLOBALS['http_log'][] = $url . $query;
	return spotify_http()->get($url . $query);
}

function spotify_put( string $uri, array $data ) {
	$url = strpos($uri, '://') == false ? 'https://api.spotify.com/' . $uri : $uri;
	$GLOBALS['http_log'][] = $url;
	return spotify_http()->put($url, [
		'body' => json_encode($data),
	]);
}

function spotify_get_playlists() {
	$total = -1;
	$playlists = [];
	while ( $total == -1 || count($playlists) < $total ) {
		$rsp = spotify_get('v1/me/playlists', ['offset' => count($playlists), 'limit' => 50]);
		$json = (string) $rsp->getBody();
		$data = json_decode($json, true);
		if ( !isset($data['items']) ) {
			throw new Exception($json);
		}
		$total = $data['total'];
		foreach ($data['items'] as $playlist) {
			$playlists[ $playlist['id'] ] = $playlist;
		}
	}

	return $playlists;
}
