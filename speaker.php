<?php

require 'common.php';

$queue = loadJSON('queue.json');

// キューが空になったとき、nowplaying を取り出す
if(($nowplaying = array_shift($queue)) === null){
	$nowplaying = loadJSON('nowplaying.json', $nowplaying);
}

saveJSON('queue.json', $queue);
saveJSON('nowplaying.json', $nowplaying);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BGM Share 4</title>
<link rel="stylesheet" type="text/css" href="cssbase-min.css">
<script src="jquery-3.3.1.min.js"></script>
<script>

<?php
if(strpos($nowplaying->url, 'https://www.youtube.com/watch?v=') === 0){
	$videoid = substr($nowplaying->url, 32);
?>

function initialize(){
	$("body").append('<script src="https://www.youtube.com/iframe_api"><\/script>');
}

function onYouTubeIframeAPIReady(){
	var player = new YT.Player("control", {
		width: "320",
		height: "240",
		videoId: "<?php print $videoid; ?>",
		playerVars: {
			autoplay: 1
		},
		events: {
			onStateChange: function (){
				if(player.getPlayerState() === YT.PlayerState.ENDED){
					window.location.reload();
				}
			},
			onError: function () {
				window.location.reload();
			}
		}
	});
}


<?php
}else if(strpos($nowplaying->url, 'files/') === 0){
	$source = $nowplaying->url;
?>

function initialize(){
	var player = $('<audio src="<?php print $source; ?>" controls autoplay></audio>')
		.on("ended", function (){
			window.location.reload();
		});
	$("#control").append(player);
}

<?php
}
?>

$(document).ready(initialize);

</script>
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
				<h2>Control</h2>
				<div>
					<div id="control"></div>
				</div>
			</section>
		</div>
	</div>
	<footer>
		<div></div>
	</footer>
</div>
</body>
</html>
