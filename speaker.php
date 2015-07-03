<?php

$queue = file_get_contents(__DIR__ . "/queue.json");
$queue = json_decode($queue);

if(($nowplaying = array_shift($queue)) === null){
	$nowplaying = file_get_contents(__DIR__ . "/nowplaying.json");
	$nowplaying = json_decode($nowplaying);
}

file_put_contents(__DIR__ . "/queue.json", json_encode($queue));
file_put_contents(__DIR__ . "/nowplaying.json", json_encode($nowplaying));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BGM Share 4</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssbase/cssbase-min.css">
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
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