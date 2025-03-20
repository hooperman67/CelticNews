<?php
include_once('autoloader.php');

$blogs_rss = array(
    'https://www.celticquicknews.co.uk/feed/',
    'https://readceltic.com/feed',
    'https://thecelticstar.com/feed/',
    'https://www.67hailhail.com/feed/',
    'https://celtic365.com/feed/',
    'https://celticfanzine.com/category/news/feed/',
    'http://celticunderground.net/feed/',
    'https://videocelts.com/category/blogs/latest-news/feed/',    
    'https://www.sportsmole.co.uk/football/celtic.xml'
);

$items = [];

foreach ($blogs_rss as $rss_url) {
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

    <h1>Latest Celtic Blogs</h1><div class='flex one two-600 four-1200 demo'>";

foreach ($items as $item) {
    // Extracting thumbnail from srcset attribute
    $thumbnail = '';
    $content = $item->get_content();
    @$doc = new DOMDocument();
    $doc->loadHTML($content);
    $xpath = new DOMXPath($doc);
    $srcset = $xpath->evaluate("string(//img/@srcset)");

    if (!empty($srcset)) {
        // Split the srcset into individual image sources
        $sources = explode(',', $srcset);

        // Initialize variables to keep track of the selected image URL
        $selected_url = '';

        foreach ($sources as $source) {
            $parts = explode(' ', trim($source));
            $url = trim($parts[0]);
            $width = (int)trim($parts[1], 'w'); // Remove 'w' from width and convert to integer

            // Check if this image meets the maximum width requirement
            if ($width <= 600) {
                // If the width is within the limit, select this image
                $selected_url = $url;
                break; // Stop searching once a suitable image is found
            }
        }

        // Assign the selected image URL as the thumbnail
        $thumbnail = $selected_url;
    }

    // Check if thumbnail is still empty and attempt to get it from other methods
    if (empty($thumbnail)) {
        if (null !== ($enclosure = $item->get_enclosure(0))) {
            if ($enclosure->get_thumbnail()) {
                $thumbnail = $enclosure->get_thumbnail();
            } elseif ($enclosure->get_link()) {
                $thumbnail = str_replace("_m.jpg", "_s.jpg", $enclosure->get_link());
            }
        }

}
               // Assign default thumbnail if no image URL found in <content:encoded> tag
                if (empty($thumbnail)) {
                    $thumbnail = 'images/craic.webp';
                }
                
        // Use getimagesize to get the image dimensions
        list($width, $height) = getimagesize($thumbnail);
        $width = intval($width);
        $height = intval($height);                

    $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='$thumbnail' alt='Thumbnail'>";
    $html_content .= "<h3><a href='" . $item->get_permalink() . "'>" . $item->get_title() . "</a></h3>";
    $html_content .= "<p>".shorten($item->get_description(), 400)."</p>";
    $html_content .= "Publisher: ".$item->get_feed()->get_title()."";
    $html_content .= " <p>".$item->get_date()."</p>";
    $html_content .= "</article></div>";
}

$html_content .= "</div></div><script src='myscripts.js'></script></body></html>";

// Save to file
file_put_contents('public/blogs.html', $html_content);

echo "Results saved to <a href='public/index.html'>Blogs Page</a>";
?>

