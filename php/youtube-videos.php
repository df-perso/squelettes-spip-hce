<?php
define('MAX_RESULTS', 12);
$apikey = 'AIzaSyAg5sVSU6sF0jz6NjRKI7fnCuuOQvq2U4I'; 
$googleApiUrl = 'https://www.googleapis.com/youtube/v3/playlistItems/?part=snippet,contentDetails&playlistId=PLmzLxX_KxljFx1cCaSsa8c3OEpYPWHEE8&maxResults='.MAX_RESULTS.'&key='.$apikey;

$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

curl_close($ch);
$data = json_decode($response);
$value = json_decode(json_encode($data), true);

//print_r($value);

for ($i = 0; $i < MAX_RESULTS; $i++)
{
    $videoId = $value['items'][$i]['snippet']['resourceId']['videoId'];
    $title = $value['items'][$i]['snippet']['title'];
    $description = $value['items'][$i]['snippet']['description'];

    echo '<div class="video-big-tile">';
    echo '<div  class="video-div">';
    foreach(array('maxres', 'high', 'standard') as $key)
    {
        if (array_key_exists($key, $value['items'][$i]['snippet']['thumbnails']))
        {
            echo '<img src="'.$value['items'][$i]['snippet']['thumbnails'][$key]['url'].'">';
            break;
        }
    }
	    //echo '<iframe id="iframe" style="width:100%;height:100%" src="//www.youtube.com/embed/'.$videoId.'" data-autoplay-src="//www.youtube.com/embed/'.$videoId.'?autoplay=1"></iframe>';                  
        echo '<div class="video-youtube-button" title="Cliquez pour lancer la vidéo"><span class="fa-stack fa-2x"><i class="fab fa-youtube fa-stack-2x"></i><i class="fa fa-play fa-stack-1x" style="color: #fff"></i></span></div>';
    echo '</div>'; 
    echo '<div class="video-info">';
	    echo '<div class="video-title">'.$title.'</div>';
        echo '<div class="video-description"'.$description.'</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
