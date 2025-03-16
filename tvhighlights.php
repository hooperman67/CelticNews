<?php
$feedUrls = [
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLGwqZMK224Z0zTHQsu_2oZSvpITzCqUrd",
    "https://www.youtube.com/feeds/videos.xml?playlist_id=PLubVgegS36EPiszXnSgEeop3ExbBx7Ubb",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UCakRszbIjjGYtFrDPeg5Ieg",
    "https://www.youtube.com/feeds/videos.xml?channel_id=UClPCjayqAxV1ANqfACWdZqA",
    'https://www.youtube.com/feeds/videos.xml?channel_id=UCcw05gGzjLIs5dnxGkQHMvw',
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLvij5I1MVvM0rftTEoC9QDoGonEF2eVbn", // spfl
    "http://www.youtube.com/feeds/videos.xml?playlist_id=PLaW1auH8HxvDN5Jox3YV_UO87UgCZry4I" // viaplay with your second feed URL
];

// Words to include in the title
$includeWords = ['Celtic', 'Bhoys', 'Celts'];
$excludeWords = ['Dominate', 'Clement'];
$feedEntries = [];
$outoptions = '<option value="All">All Channels</option>';
foreach ($feedUrls as $feedUrl) {
    // Load feed xml file
    $xml = simplexml_load_file($feedUrl);
    
    $media = $xml->entry->children('media', true);
    $group = $media->group;

    // Collect feed entries that include the specified words in the title
    for ($i = 0; $i < 10; $i++) {
        // Define feed nodes
        $published = $xml->entry[$i]->published;
        // Optional: Shorten the date
        $shortDate = date("m/d/Y", strtotime($published));
        $title = $xml->entry[$i]->title;
        $desc = $group->description;
        $id = $xml->entry[$i]->id;
        //strip unwanted characters from ID
        $id = str_replace("yt:video:", "", $id);
        $thumbnail = "https://img.youtube.com/vi/" . $id . "/hqdefault.jpg";
        $author = $xml->entry[$i]->author->name;
        $uri = $xml->entry[$i]->author->uri;
        
  // Check if the title contains any of the specified words
$containsWords = false;
foreach ($includeWords as $word) {
    if (stripos($title, $word) !== false) {
        $containsWords = true;
        break;
    }
}

// Check if the title contains any of the specified words to exclude
foreach ($excludeWords as $word) {
    if (stripos($title, $word) !== false) {
        $containsWords = false;
        break;
    }
}

// Collect the entry if it contains any of the specified words and does not contain any of the excluded words
if ($containsWords) {
    $feedEntries[] = [
        'publish' => $shortDate,
        'published' => $published,
        'description' => $desc,
        'author' => $author,
        'thumbnail' => $thumbnail,
        'title' => $title,
        'url' => $uri,
        'id' => $id
    ];
}        
       
    }
}
// Sort the feed entries by $shortDate in descending order
usort($feedEntries, function ($a, $b) {
    return strtotime($b['published']) - strtotime($a['published']);
});

// Display sorted feed entries
foreach ($feedEntries as $entry)
 {
      $rss .= '<div class="article" ><article class="card"><img loading="lazy" src="' . $entry['thumbnail'] . '"alt="' . $entry['title'] . '" class="img"></a>';
      $rss .= '<a target="_blank" href = "https://youtu.be/' . $entry['id'] .'">' . $entry['title'] . '</a>';
      $rss .= '<p>'. $entry['publish'] .'</p><br>';              

      $rss .= 'Celtic Highlights from '. $entry['author'] .'';   
      $rss .= '</article></div>';  
}

$template = file_get_contents('tvhighlightsbase.html');
$html = str_replace(array('<!-- posts here -->','<!-- options here -->'),array($rss,$outoptions),$template);
file_put_contents('public/tvhighlights.html', $html);
?>  
