<?php

$nowplaying = file_get_contents(__DIR__ . "/nowplaying.json");
$nowplaying = json_decode($nowplaying);
$queue = file_get_contents(__DIR__ . "/queue.json");
$queue = json_decode($queue);

if($youtube = trim($_POST["youtube"])){
	if(strpos($youtube, 'https://www.youtube.com/watch?v=') !== 0
	&& strpos($youtube, 'https://www.youtube.com/playlist?list=') !== 0){
		exit("not youtube url");
	}
	if (isPlaylist($youtube)) {
		$urls = getPlaylistUrls($youtube);
		pushUrls($urls, $queue);
	} else {
		pushUrls([$youtube], $queue);
	}
}else if($file = $_FILES["file"]){
	$file_pathinfo = pathinfo($file["name"]);
	$filename = md5_file($file["tmp_name"]) . "." . $file_pathinfo["extension"];

	if($file["size"] > 20000000){
		exit("too large file");
	}
	if(!in_array($file_pathinfo["extension"], array("mp3"))){
		exit("not allowed extension");
	}
	if(!is_uploaded_file($file["tmp_name"]) || $file["error"] > 0){
		exit("uploading error " . $file["error"]);
	}

	move_uploaded_file($file["tmp_name"], __DIR__ . "/files/" . $filename);
	$playing = (object) array(
		"url" => "files/$filename",
		"title" => $file["name"],
		"thumb" => "files/file.png",
		"user" => $_SERVER["REMOTE_ADDR"]
	);
	if(end($queue)->url !== $playing->url){
		array_push($queue, $playing);
		file_put_contents(__DIR__ . "/queue.json", json_encode($queue));
	}
}

function isPlaylist($url) {
	$query = parse_url($url, PHP_URL_QUERY);
	foreach(explode("&", $query) as $keyVal) {
		list($key, $_) = explode("=", $keyVal);
		if ($key == "list") {
			return true;
		}
	}
	return false;
}

function getPlaylistUrls($url) {
	$str = file_get_contents($url);

	$ids = array_map(function ($row) {
		if (strlen($row) > 1000) {
			return;
		}
		preg_match('/"\/watch\?v=([^&]+).+;index=[0-9]+/u', $row, $matches);
		if (empty($matches)) {
			return;
		}
		return $matches[1];
	}, explode("\n", $str));

	return array_map(function ($id) {
		return sprintf("https://www.youtube.com/watch?v=%s", $id);
	}, array_filter(array_unique($ids)));
}

function pushUrls($urls, &$queue) {
	$before = count($queue);
	foreach($urls as $url) {
		if(($otherparam = strpos($url, '&')) !== false){
			$url = substr($url, 0, $otherparam);
		}
		$videoid = substr($url, 32);
		$playing = (object) array(
			"url" => $url,
			"title" => $url,
			"thumb" => "http://i.ytimg.com/vi/$videoid/default.jpg",
			"user" => $_SERVER["REMOTE_ADDR"]
		);
		if(end($queue)->url !== $playing->url){
			array_push($queue, $playing);
		}
	}

	if ($before < count($queue)) {
		file_put_contents(__DIR__ . "/queue.json", json_encode($queue));
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BGM Share 4</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssbase/cssbase-min.css">
<style>

div#addqueue{
	background-color: #ffffd1;
}

</style>
</head>

<body>
<div id="container">
	<header>
		<div>
			<h1>BGM Share 4 (Alpha release)</h1>
		</div>
	</header>
	<div id="main">
		<div id="player">
			<section>
				<div id="nowplaying">
					<h2>Now Playing</h2>
					<div>
						<div id="thumbbox">
							<div><img src="<?php print $nowplaying->thumb; ?>" alt="サムネイル"></div>
							<div><?php print $nowplaying->title; ?></div>
							<div>from <?php print $nowplaying->user; ?></div>
						</div>
					</div>
				</div>
			</section>
			<section>
				<div id="addqueue">
					<form action="." method="post" enctype="multipart/form-data">
						<h3>Youtube URL</h3>
						<div>
							<input type="text" name="youtube"><input type="submit" value="Enqueue">
						</div>
						<div>OR</div>
						<h3>Local File</h3>
						<div>
							<input type="file" name="file"><input type="submit" value="Enqueue">
						</div>
					</form>
				</div>
			</section>
			<section>
				<div id="queue">
					<ol>
<?php
	foreach($queue as $value){
?>
						<li>
							<div id="thumbbox">
								<div><img src="<?php print $value->thumb; ?>" alt="サムネイル"></div>
								<div><?php print $value->title; ?></div>
								<div>from <?php print $value->user; ?></div>
							</div>
						</li>
<?php
	}
?>
					</ol>
				</div>
			</section>
		</div>
<!--
		<div id="room">
			<section>
				<div id="about">
					<h2>My Room</h2>
					<p>Description</p>
					<div></div>
				</div>
			</section>
			<section>
				<div id="member">
					<ul></ul>
				</div>
			</section>
		</div>
-->
	</div>
	<footer>
		<div></div>
	</footer>
</div>
</body>
</html>
