<?php
include_once('autoloader.php');

$highlights_rss = array(
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLGwqZMK224Z0zTHQsu_2oZSvpITzCqUrd",
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLubVgegS36EPiszXnSgEeop3ExbBx7Ubb",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCakRszbIjjGYtFrDPeg5Ieg",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UClPCjayqAxV1ANqfACWdZqA",
    'https://www.youtube.com/feeds/videos.xml?channel_id=UCcw05gGzjLIs5dnxGkQHMvw',
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLvij5I1MVvM0rftTEoC9QDoGonEF2eVbn", // spfl
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLaW1auH8HxvDN5Jox3YV_UO87UgCZry4I" // viaplay with your second feed URL
);

// Words to include in the title
$includeWords = ['Celtic', 'Bhoys', 'Celts'];
$excludeWords = ['Dominate', 'Clement'];

$items = [];

foreach ($highlights_rss as $rss_url) {
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

    <h1>Latest Celtic FC Highlights and Interviews</h1><div class='flex one two-600 four-1200 demo'>";

$filteredItems = [];

$filteredItems = array_filter($items, function ($item) use ($includeWords, $excludeWords) {
    $title = $item->get_title();
    if (!$title) return false;

    // Check if title contains any of the include words
    foreach ($includeWords as $word) {
        if (stripos($title, $word) !== false) {
            // Ensure it does not contain any exclude words
            foreach ($excludeWords as $word) {
                if (stripos($title, $word) !== false) {
                    return false;
                }
            }
            return true;
        }
    }
    return false;
});

// Now sort
usort($filteredItems, function ($a, $b) {
    return $b->get_date('U') <=> $a->get_date('U');
});

// Slice to 15 results
$filteredItems = array_slice($filteredItems, 0, 15);



foreach ($filteredItems as $item) {
    $thumb = $item->get_enclosure(0)->get_thumbnail();

    $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='$thumb' alt='Thumbnail'>";
    $html_content .= "<h3><a href='" . $item->get_permalink() . "'>" . $item->get_title() . "</a></h3>";
    $html_content .= "<p>" . shorten($item->get_description(), 400) . "</p>";
    $html_content .= "Publisher: " . $item->get_feed()->get_title() . "";
    $html_content .= " <p>" . $item->get_date() . "</p>";
    $html_content .= "</article></div>";
}

$html_content .= "</div></div><script src='myscripts.js'></script></body></html>";
// Save to file
file_put_contents('public/tvhighlights.html', $html_content);

echo "Results saved to <a href='public/tvhighlights.html'>tvhighlights Page</a>";

