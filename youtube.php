<?php

    include_once('SimplePie.compiled.php');

$youtube_rss = array(
        'https://www.youtube.com/feeds/videos.xml?channel_id=UC40iYWGZDD1cC4zvYRCWjHw',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCBN-bb-hE7jYlcp4exwXRsQ',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCqUPn73T2WxGyzCdtLe8m7g',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCm39DIOf_A2tOKswod6PrUQ',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCpu4A47KwktyCPj_d9w-ALQ',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCk-Y0J8-BUpUG_aIQJ9ZWXg',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCbzj0IDJjLzRzCiL4Ylbuiw',
        'https://www.youtube.com/feeds/videos.xml?channel_id=UCrHWCUDb945_ar1vLoYxJ2w'
);

// Create instances for each array
$youtube_feed = new \SimplePie();

// Set feed URLs for each instance
$youtube_feed->set_feed_url($youtube_rss);

$youtube_feed->set_item_limit(14);

$youtube_feed->strip_htmltags(array_merge($youtube_feed->strip_htmltags, array('p', 'em')));

// Initialize feeds
$youtube_feed->init();


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

    $youtubeitems = [];
$yt = '';  // Initialize rss3 as an empty string
$imageCount = 0;
$headingAdded = false;  // Initialize the flag for the heading
      
    foreach($youtube_feed->get_items(0,20) as $item) {
      $media_group = $item->get_item_tags('', 'enclosure');

$thumb = $item->get_enclosure(0)->get_thumbnail();
       list($width, $height) = getimagesize($thumb);
        $width = intval($width);
        $height = intval($height); 
    
  $youtube_feed = $item->get_feed(); 
     

    // Add to RSS feed
    if ($imageCount < 9 && !empty($thumb)) {
$yt .= '<div class="article">';
$yt .= '<article class="card">';
$yt .= '<img src="'. $thumb .'" alt="'. $item->get_title() .'" width="300px" class="img">';
$yt .= '<h3><a rel="nofollow" target="_blank" href="'. $item->get_permalink() .'">'. $item->get_title() .'</a></h3>';
$yt .= '<p>'. $item->get_date() .'</p>';
$yt .= 'Video from: '.$item->get_feed()->get_title().'';
$yt .= '</article></div>';

        $imageCount++;
    } else {
        // Add the heading only once before the first article without images
        if (!$headingAdded) {
            $yt .= '</div><div class="section"><h4 class="center">Recent Celtic Videos</h4>';
            $headingAdded = true;
        }


        $yt .= '<button class="accordion"><h3>'. $item->get_title() .'</h3></button>';
        $yt .= '<div class="panel"><p>'. $item->get_date() .'</p>';
        $yt .= 'Video from : '.$item->get_feed()->get_title().'<br>';
        $yt .= '<a rel="nofollow" target="_blank" href="' . $item->get_permalink() . '"> Watch on site</a>';        
        $yt .= '<p><a target="_blank" href="https://twitter.com/intent/tweet/?text='.urlencode($item->get_title()).'&url='. urlencode($item->get_permalink()) .'"><img src="images/twitter.svg" width="32px" height="32px" alt="Twitter"> Share</a></p><br>';
        $yt .= '</div>';
    }
}

$template = file_get_contents('youtubebase.html');
$html = str_replace('<!-- posts here -->', $yt, $template);
file_put_contents('public/youtube.html', $html);
