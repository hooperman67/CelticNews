<?php
include_once('autoloader.php');

$news_rss = array(
    'https://www.dailyrecord.co.uk/all-about/celtic-fc/?service=rss',
    'https://www.scotsman.com/sport/football/celtic/rss',
    'https://feeds.bbci.co.uk/sport/6d397eab-9d0d-b84a-a746-8062a76649e5/rss.xml',
    'https://www.glasgowtimes.co.uk/sport/celtic/rss/',
    'https://news.stv.tv/topic/celtic/feed',
    'https://www.express.co.uk/posts/rss/67.99/celtic',
    'https://www.footballscotland.co.uk/all-about/celtic-fc?service=rss',
    'https://www.glasgowworld.com/sport/football/celtic/rss',
    'https://www.glasgowlive.co.uk/all-about/celtic-fc/?service=rss'
);

$items = [];

foreach ($news_rss as $rss_url) {
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

// Generate HTML content
$html_content = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel="canonical" href="https://hooperman67.github.io/CelticNews/index.html" />
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

    <h1>Latest Celtic FC News</h1><div class='flex one two-600 four-1200 demo'>";

foreach ($items as $item) {
 if (null !== ($enclosure = $item->get_enclosure(0))) {
            // Output enclosure properties
            if ($enclosure->get_link() && $enclosure->get_type()) {
                $type = $enclosure->get_type();
                $size = $enclosure->get_size() ? $enclosure->get_size() . ' MB' : '';
               // echo "Enclosure Type: $type, Size: $size\n";
            }
            
            // Output thumbnail if available
            if ($enclosure->get_thumbnail()) {
                $thumbnail = $enclosure->get_thumbnail();
               // echo "Thumbnail: $thumbnail\n";
            }

            if ($enclosure->get_link()) {
                $thumbnail = str_replace("_m.jpg","_s.jpg" , $enclosure->get_link());
               // echo "Thumbnail: $thumbnail\n";
            }
    
            // You had an incomplete if block here, I'll correct it below
            if ($return = $item->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'thumbnail')) {
                $thumbnail_attribs = $return[0]['attribs'];
                // Do something with $thumbnail_attribs if needed
            }
            
        // Use getimagesize to get the image dimensions
        list($width, $height) = getimagesize($thumbnail);
        $width = intval($width);
        $height = intval($height);               
            }

    // Get and clean the description (remove <img> tags)
    $description = $item->get_description();
    $description = preg_replace('/<img[^>]+>/', '', $description); // Remove all <img> tags


    $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='$thumbnail' alt='Thumbnail'>";
    $html_content .= "<h3><a href='" . $item->get_permalink() . "'>" . $item->get_title() . "</a></h3>";
    $html_content .= "<p>$description</p>";
    $html_content .= "Publisher: ".$item->get_feed()->get_title()."";
    $html_content .= " <p>".$item->get_date()."</p>";
    $html_content .= "</article></div>";
}

$html_content .= "</div></div><script src='myscripts.js'></script></body></html>";

// Save to file
file_put_contents('public/index.html', $html_content);

echo "Results saved to <a href='public/index.html'>News Page</a>";
?>

