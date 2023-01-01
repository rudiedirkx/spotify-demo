<?php

require 'inc.bootstrap.php';

require 'inc.auth.php';

$playlists = spotify_get_playlists();

if ( isset($_POST['playlists']) ) {
	header('Content-type: text/plain; charset=utf-8');
	foreach ( $playlists as $id => $playlist ) {
		$isPublic = (bool) $playlist['public'];
		$newPublic = !empty($_POST['playlists'][$id]['public']);
		if ( $isPublic != $newPublic ) {
			$rsp = spotify_put($playlist['href'], [
				'public' => $newPublic,
			]);
			if ( $rsp->getStatusCode() != 200 ) {
				$body = (string) $rsp->getBody();
echo '<pre>' . $body . '</pre>';
dump($rsp);
exit;
				throw new Exception($body);
			}
			usleep(50000);
		}
	}

	do_redirect(null);
	exit;
}

?>
<title>Playlists</title>

<form method="post" action>
	<input type="hidden" name="playlists[0][public]" value="0" />
	<table border="1">
		<? foreach ($playlists as $id => $playlist): ?>
			<tr>
				<td><?= html($playlist['name']) ?></td>
				<td><input name="playlists[<?= html($id) ?>][public]" type="checkbox" <?= $playlist['public'] ? 'checked' : '' ?> /></td>
			</tr>
		<? endforeach ?>
	</table>
	<p><input type="submit" /></p>
</form>

<details>
	<summary>Playlist</summary>
	<? dump(reset($playlists)) ?>
</details>

<?php

include 'tpl.footer.php';
