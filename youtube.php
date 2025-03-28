<?php
include_once('autoloader.php');

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

$items = [];

foreach ($youtube_rss as $rss_url) {
    $feed = new \SimplePie();
    $feed->set_feed_url($rss_url);
    $feed->enable_cache(false); // Optional: Disable caching for fresh results
    $feed->set_item_limit(15); // Limit each feed to 15 items
    $feed->init();
    $feed->handle_content_type();
    
    if ($feed->error()) {
        echo "Error fetching feed: " . $feed->error() . "<br>";
    } else {
        $items = array_merge($items, $feed->get_items());
    }
}

// Sort items by date (newest first)
usort($items, function ($a, $b) {
    return $b->get_date('U') <=> $a->get_date('U');
});

// Limit final output to 15 items
$items = array_slice($items, 0, 16);

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

// Generate HTML content
$html_content = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>RSS Feed Results</title>
    <link rel='stylesheet' href='myminified.css'>
</head>
<body>
<div style='overflow: hidden;height: 50px;'>
<nav>
  <a href='https://armchaircelts.co.uk/' class='brand'>
    <span>ArmchairCelts</span>
  </a>

  <input id='bmenub' type='checkbox' class='show'>
  <label for='bmenub' class='burger pseudo button'>menu</label>

  <div class='menu'>
    <a href='index.html' class='button success'>News</a>
    <a href='blogs.html' class='button success'>Blogs</a>
    <a href='podcasts.html' class='button success'>Podcasts</a>
    <a href='youtube.html' class='button success'>Videos</a>
    <a href='tvhighlights.html' class='button success'>TV Highlights</a>
  </div>
</nav>

</div>
<div class='container'>

    <h1>Latest Celtic FC Youtubers</h1><div class='flex one two-600 four-1200 demo'>";

foreach ($items as $item) {

      $media_group = $item->get_item_tags('', 'enclosure');

$thumb = $item->get_enclosure(0)->get_thumbnail();
       list($width, $height) = getimagesize($thumb);
        $width = intval($width);
        $height = intval($height); 
    $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='$thumb' alt='Thumbnail'>";
    $html_content .= "<h3><a href='" . $item->get_permalink() . "'>" . $item->get_title() . "</a></h3>";
    $html_content .= "<p>".shorten($item->get_description(), 400)."</p>";
    $html_content .= "Publisher: ".$item->get_feed()->get_title()."";
    $html_content .= " <p>".$item->get_date()."</p>";
    $html_content .= "</article></div>";
}

$html_content .= "</div></div><script src='nav.js'></script></body></html>";

// Save to file
file_put_contents('docs/youtube.html', $html_content);

echo "Results saved to <a href='docs/youtube.html'>Youtube Page</a>";
?>

