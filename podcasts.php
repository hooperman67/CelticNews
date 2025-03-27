<?php
include_once('autoloader.php');

$podcasts_rss = array(
    'https://www.spreaker.com/show/2287253/episodes/feed',
    'https://shows.acast.com/60e717946b59de00120e3e44',
    'https://feeds.acast.com/public/shows/5f208eec15e9d83c37daa234',
    'https://4tims.podomatic.com/rss2.xml',
    'https://www.spreaker.com/show/1544444/episodes/feed',
    'https://www.spreaker.com/show/5155742/episodes/feed', 
    'https://feeds.soundcloud.com/users/soundcloud:users:104114898/sounds.rss'
);

$items = [];

foreach ($podcasts_rss as $rss_url) {
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
$items = array_slice($items, 0, 12);

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
     <link rel='canonical' href='https://hooperman67.github.io/CelticNews/podcasts.html' />
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

    <h1>Latest Celtic Podcasts</h1><div class='flex one two-600 four-1200 demo'>";

foreach ($items as $item) {

// 1. Try to get the iTunes image
$image_tags = $item->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'image');
if (!empty($image_tags[0]['attribs']['']['href'])) {
    $thumb = $image_tags[0]['attribs']['']['href'];
}

// 2. If iTunes image is missing, try the standard RSS <image> tag
elseif ($item->get_feed()->get_image_url()) {
    $thumb = $item->get_feed()->get_image_url();
}

// 3. If both fail, use a fallback
else {
    $thumb = 'public/images/craic.webp'; // Change this to your default image
}

        list($width, $height) = getimagesize($thumb);
        $width = intval($width);
        $height = intval($height);        
        $date = $item->get_date("Y-m-d H:i");
        $feed_title = $item->get_feed()->get_title();
        $link = $item->get_permalink();
        $title = $item->get_title();
        $enclosure_url = $item->get_enclosure()->get_link();
        $desc = $item->get_description();
      


    $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='$thumb' alt='Thumbnail'>";
    $html_content .= "<h3><a href=''$link''>" . $item->get_title() . "</a></h3>";
    $html_content .= "<p><audio controls src='$enclosure_url'</audio></p>";
    $html_content .= "Publisher: ".$item->get_feed()->get_title()."";
    $html_content .= " <p>".$item->get_date()."</p>";
    $html_content .= "</article></div>";

}

$html_content .= "</div></div><script src='nav.js'></script></body></html>";

// Save to file
file_put_contents('docs/podcasts.html', $html_content);
echo "Results saved to <a href='docs/podcasts.html'>Podcasts Page</a>";
?>
       

