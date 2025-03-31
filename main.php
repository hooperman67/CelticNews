<?php
require_once 'autoloader.php'; // Load SimplePie

$news_rss = [
    "https://www.dailyrecord.co.uk/all-about/celtic-fc/?service=rss",
    "https://www.scotsman.com/sport/football/celtic/rss",
    "https://feeds.bbci.co.uk/sport/6d397eab-9d0d-b84a-a746-8062a76649e5/rss.xml",
    "https://www.glasgowtimes.co.uk/sport/celtic/rss/",
    "https://news.stv.tv/topic/celtic/feed",
    "https://www.express.co.uk/posts/rss/67.99/celtic",
    "https://www.footballscotland.co.uk/all-about/celtic-fc?service=rss",
    "https://www.glasgowworld.com/sport/football/celtic/rss",
    "https://www.glasgowlive.co.uk/all-about/celtic-fc/?service=rss"
];

$blogs_rss = [
    "https://www.celticquicknews.co.uk/feed/",
    "https://readceltic.com/feed",
    "https://thecelticstar.com/feed/",
    "https://www.67hailhail.com/feed/",
    "https://celtic365.com/feed/",
    "https://celticfanzine.com/category/news/feed/",
    "http://celticunderground.net/feed/",
    "https://videocelts.com/category/blogs/latest-news/feed/",    
    "https://www.sportsmole.co.uk/football/celtic.xml"
];

$podcasts_rss = [
    "https://rss.acast.com/acelticstateofmind",
    "https://www.spreaker.com/show/2287253/episodes/feed",
    "https://shows.acast.com/60e717946b59de00120e3e44",
    "https://feeds.acast.com/public/shows/5f208eec15e9d83c37daa234",
    "https://4tims.podomatic.com/rss2.xml",
    "https://www.spreaker.com/show/1544444/episodes/feed",
    "https://www.spreaker.com/show/5155742/episodes/feed", 
    "https://feeds.soundcloud.com/users/soundcloud:users:104114898/sounds.rss"
];

$youtube_rss = [
    "https://www.youtube.com/feeds/videos.xml?channel_id=UC40iYWGZDD1cC4zvYRCWjHw",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCBN-bb-hE7jYlcp4exwXRsQ",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCqUPn73T2WxGyzCdtLe8m7g",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCm39DIOf_A2tOKswod6PrUQ",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCpu4A47KwktyCPj_d9w-ALQ",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCk-Y0J8-BUpUG_aIQJ9ZWXg",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCbzj0IDJjLzRzCiL4Ylbuiw",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCrHWCUDb945_ar1vLoYxJ2w"  
];

$highlights_rss = [
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLGwqZMK224Z0zTHQsu_2oZSvpITzCqUrd",
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLubVgegS36EPiszXnSgEeop3ExbBx7Ubb",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCakRszbIjjGYtFrDPeg5Ieg",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UClPCjayqAxV1ANqfACWdZqA",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCcw05gGzjLIs5dnxGkQHMvw",
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLvij5I1MVvM0rftTEoC9QDoGonEF2eVbn", // spfl
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLaW1auH8HxvDN5Jox3YV_UO87UgCZry4I" // viaplay
];

// Words to include in the title (Only for 'highlights' category)
$includeWords = ['Celtic', 'Bhoys', 'Celts'];
$excludeWords = ['Dominate', 'Clement'];

$feeds = [
    'news' => $news_rss,
    'blogs' => $blogs_rss,
    'podcasts' => $podcasts_rss,
    'youtube' => $youtube_rss,
    'highlights' => $highlights_rss
];

foreach ($feeds as $type => $feed_urls) {
    $items = []; // Reset items for each category

    $html_content = "<html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>" . ucfirst($type) . " RSS Feed</title><link rel='stylesheet' href='css/style.css'>";
    $html_content .= "<link rel='canonical' href='https://armchaircelts.co.uk/$type.html' /></head><body>";
    
    
    $html_content .= "<div style='overflow: hidden;height: 50px;'>
<nav>
  <a href='index.html' class='brand'>
    <span>Celtic FC News</span>
  </a>

  <input id='bmenub' type='checkbox' class='show'>
  <label for='bmenub' class='burger pseudo button'>menu</label>

  <div class='menu'>
    <a href='news.html' class='button success'>News</a>
    <a href='blogs.html' class='button success'>Blogs</a>
    <a href='podcasts.html' class='button success'>Podcasts</a>
    <a href='youtube.html' class='button success'>Videos</a>
    <a href='highlights.html' class='button success'>TV Highlights</a>
  </div>
</nav>

</div>
<div class='container'>";
    $html_content .= "<h1>" . ucfirst($type) . " Feed</h1><div class='flex one two-600 four-1200 demo'>";

    foreach ($feed_urls as $url) {
        $feed = new SimplePie();
        $feed->set_feed_url($url);
        $feed->enable_cache(false);
        $feed->init();

        if ($feed->error()) {
            echo "Error fetching feed: " . htmlspecialchars($feed->error()) . "<br>";
            continue;
        }

        foreach ($feed->get_items() as $item) {
            $title = $item->get_title();

            // Apply filtering only for highlights_rss
            if ($type === 'highlights') {
                $titleLower = strtolower($title); // Convert title to lowercase for case-insensitive matching
                
                // Check if at least one include word exists
                $includeMatch = empty($includeWords) || array_filter($includeWords, fn($word) => stripos($title, $word) !== false);
                
                // Check if any exclude word exists
                $excludeMatch = array_filter($excludeWords, fn($word) => stripos($title, $word) !== false);
                
                // Skip item if it doesn't match include words OR contains exclude words
                if (!$includeMatch || $excludeMatch) {
                    continue;
                }
            }

            $items[] = $item;
        }
    }

    // Sort items by date (newest first)
    usort($items, fn($a, $b) => $b->get_date('U') <=> $a->get_date('U'));

    // Limit to 16 items
    $items = array_slice($items, 0, 16);

    // Output sorted and filtered items
    foreach ($items as $item) {

        $title = htmlspecialchars($item->get_title());
        $link = htmlspecialchars($item->get_permalink());
        $description = htmlspecialchars(strip_tags($item->get_description()));
        $date = $item->get_date('"F d, Y h:i A"');

        $thumbnail = '';
        $audio_url = '';
    
        // Extract srcset images
        $content = $item->get_content();
        @$doc = new DOMDocument();
        @$doc->loadHTML($content);
        $xpath = new DOMXPath($doc);
        $srcset = $xpath->evaluate("string(//img/@srcset)");
    
        if (!empty($srcset)) {
            $sources = explode(',', $srcset);
            foreach ($sources as $source) {
                $parts = explode(' ', trim($source));
                $url = trim($parts[0]);
                $width = (int)trim($parts[1], 'w'); // Convert width to integer
    
                if ($width <= 600) {
                    $thumbnail = $url;
                    break;
                }
            }
        }
    
        $thumbnail = '';
        $audio_url = '';
    
        // Extract srcset images
        $content = $item->get_content();
        @$doc = new DOMDocument();
        @$doc->loadHTML($content);
        $xpath = new DOMXPath($doc);
        $srcset = $xpath->evaluate("string(//img/@srcset)");
    
        if (!empty($srcset)) {
            $sources = explode(',', $srcset);
            foreach ($sources as $source) {
                $parts = explode(' ', trim($source));
                $url = trim($parts[0]);
                $width = (int)trim($parts[1], 'w'); // Convert width to integer
    
                if ($width <= 600) {
                    $thumbnail = $url;
                    break;
                }
            }
        }
    
        // Check iTunes image first (to avoid assigning MP3 file as an image)
        if (empty($thumbnail)) {
            $image_tags = $item->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'image');
            if (!empty($image_tags) && isset($image_tags[0]['attribs']['']['href'])) {
                $thumbnail = $image_tags[0]['attribs']['']['href'];
            }
        }
    
        // Check enclosure images if srcset & iTunes image are empty
        if (empty($thumbnail)) {
            if (null !== ($enclosure = $item->get_enclosure(0))) {
                if ($enclosure->get_thumbnail()) {
                    $thumbnail = $enclosure->get_thumbnail();
                } elseif ($enclosure->get_link() && strpos($enclosure->get_type(), 'image') !== false) {
                    $thumbnail = str_replace("_m.jpg", "_s.jpg", $enclosure->get_link());
                }
            }
        }
    
        // Extract MP3 file only (skip images)
        $enclosures = $item->get_enclosures();
        if (!empty($enclosures)) {
            foreach ($enclosures as $enclosure) {
                if ($enclosure->get_link() && strpos($enclosure->get_type(), 'audio') !== false) {
                    $audio_url = $enclosure->get_link();
                    break; // Use the first audio file found
                }
            }
        }
    
        // Assign default thumbnail if still empty
        if (empty($thumbnail)) {
            $thumbnail = 'images/clover.png';
        }       

        $html_content .= "<div>
    <article class='card'><img class='thumbnail' src='{$thumbnail}' alt='Thumbnail'>";        
        $html_content .= "<h2><a href='{$link}' target='_blank'>{$title}</a></h2>";
        $html_content .= "<p>{$description}</p>";

        $html_content .= "<p><small>Published: {$date}</small></p>";
        
        // Only add audio player if an MP3 file exists
        if (!empty($audio_url)) {
            $html_content .= "<audio controls>
                                <source src='{$audio_url}' type='audio/mpeg'>
                                Your browser does not support the audio element.
                              </audio>";
        }
        
        $html_content .= "<br><span><a target='_blank' href='https://bsky.app/intent/compose?text={$link}'><img src='images/bluesky.svg' width='32px' height='32px' alt='Bluesky'>Share</a></span></article></div>";
        
    }


    $html_content .= "</div></div></body></html>";

    // Write to separate HTML files
    file_put_contents("docs/$type.html", $html_content);
}

echo "Feeds processed successfully!";
?>

