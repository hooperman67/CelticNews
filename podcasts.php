<?php

    include_once('SimplePie.compiled.php');

$podcasts_rss = array(
    'https://www.spreaker.com/show/2287253/episodes/feed',
    'https://feed.podbean.com/celtichuddlepodcast/feed.xml',
    'https://shows.acast.com/60e717946b59de00120e3e44',
    'https://feeds.acast.com/public/shows/5f208eec15e9d83c37daa234',
    'https://4tims.podomatic.com/rss2.xml',
    'https://www.spreaker.com/show/1544444/episodes/feed',
    'https://www.spreaker.com/show/5155742/episodes/feed', 
    'https://feeds.soundcloud.com/users/soundcloud:users:104114898/sounds.rss'
);

// Create instances for each array
$podcasts_feed = new \SimplePie();

// Set feed URLs for each instance
$podcasts_feed->set_feed_url($podcasts_rss);

$podcasts_feed->set_item_limit(14);

$podcasts_feed->strip_htmltags(array_merge($podcasts_feed->strip_htmltags, array('p', 'em')));

// Initialize feeds
$podcasts_feed->init();


     function shorten($string, $length)
{
    // By default, an ellipsis will be appended to the end of the text.
    $suffix = '&hellip;';

    $short_desc = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));
 
    // Cut the string to the requested length, and strip any extraneous spaces 
    // from the beginning and end.
    $desc = trim(substr($short_desc, 0, $length));
 
    // Find out what the last displayed character is in the shortened string
    $lastchar = substr($desc, -1, 1);
 
    // If the last character is a period, an exclamation point, or a question 
    // mark, clear out the appended text.
    if ($lastchar == '.' || $lastchar == '!' || $lastchar == '?') $suffix='';
 
    // Append the text.
    $desc .= $suffix;
 
    // Send the new description back to the page.
    return $desc;
}

$rss3 = '';  // Initialize rss3 as an empty string
$imageCount = 0;
$headingAdded = false;  // Initialize the flag for the heading
      
foreach($podcasts_feed->get_items(0, 17) as $item){

    $image_tags = $item->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'image');   
    if (isset($image_tags[0]['attribs']['']['href'])) {
        $thumb = $image_tags[0]['attribs']['']['href'];
       
        list($width, $height) = getimagesize($thumb);
        $width = intval($width);
        $height = intval($height);        
        $date = $item->get_date("Y-m-d H:i");
        $feed_title = $item->get_feed()->get_title();
        $link = $item->get_permalink();
        $title = $item->get_title();
        $enclosure_url = $item->get_enclosure()->get_link();
        $desc = $item->get_description();
      
        $podcastitems[] = [
            "title" => $title,
            "date" => $date,
            "feed_title" => $feed_title,
            "link" => $link,
            "description" => $desc,
            "enclosure_url" => $enclosure_url,
            "thumb"=> $thumb,         
        ];

    // Add to RSS feed
    if ($imageCount < 6 && !empty($thumb)) {

$rss3 .= '<div class="article">';
$rss3 .= '<article class="card">';
$rss3 .= '<img src="'. $thumb .'" alt="'. $title .'" width="'.$width.'" height="'.$height.'" class="img">';
$rss3 .= '<h3><a rel="nofollow" target="_blank" href="'. $link .'">'. $title .'</a></h3>';
$rss3 .= '<p>'. $date .'</p><p><audio controls src="'. $enclosure_url.'"</audio></p><p>'. shorten($desc, 150) . '</p>';
$rss3 .= 'News Article from: '.$item->get_feed()->get_title().'';
$rss3 .= '</article></div>';

        $imageCount++;
    } else {
        // Add the heading only once before the first article without images
        if (!$headingAdded) {
            $rss3 .= '</div><div class="section"><h4 class="center">Recent Podcasts</h4>';
            $headingAdded = true;
        }


        $rss3 .= '<button class="accordion"><h3>' . $title . '</h3></button>';
        $rss3 .= '<div class="panel"><p>'. $date .'</p>';
        $rss3 .= 'Podcast from : '.$item->get_feed()->get_title().'<br><p><audio controls src="'. $enclosure_url.'"</audio></p>';      
         $rss3 .= '</div>';       
        }
  
    }
  
}

$template = file_get_contents('podcastsbase.html');
$html = str_replace('<!-- posts here -->', $rss3, $template);
file_put_contents('public/podcasts.html', $html);
