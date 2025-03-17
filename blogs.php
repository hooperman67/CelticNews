<?php

    include_once('SimplePie.compiled.php');

$blogs_rss = array(
    'https://www.celticquicknews.co.uk/feed/',
    'https://readceltic.com/feed',
    'https://thecelticstar.com/feed/',
    'https://celtic365.com/feed/',
    'https://celticfanzine.com/category/news/feed/',
    'http://celticunderground.net/feed/',
    'https://celticbynumberscom.ipage.com/feed/',
    'https://videocelts.com/category/blogs/latest-news/feed/',    
    'https://www.sportsmole.co.uk/football/celtic.xml'
);

// Create instances for each array
$blogs_feed = new \SimplePie();

// Set feed URLs for each instance
$blogs_feed->set_feed_url($blogs_rss);

$blogs_feed->set_item_limit(15);

$blogs_feed->strip_htmltags(array_merge($blogs_feed->strip_htmltags, array('p', 'em')));

// Initialize feeds
$blogs_feed->init();


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

    $blogsitems = [];
$rss2 = '';  // Initialize rss1 as an empty string
$imageCount = 0;
$headingAdded = false;  // Initialize the flag for the heading
      
    $blogitems = [];  
foreach ($blogs_feed->get_items(0, 20) as $item) {
    $blogs_feed = $item->get_feed(); 
    
    // Extracting thumbnail from srcset attribute
    $thumbnail = '';
    $content = $item->get_content();
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($content);
    libxml_clear_errors();
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
                } else {
    // Handle the missing file case here
    error_log("Image file not found: " . $imagePath);
}
                
        // Use getimagesize to get the image dimensions
        list($width, $height) = getimagesize($thumbnail);
        $width = intval($width);
        $height = intval($height);                
   $blogs_feed = $item->get_feed();
   
    if ($imageCount < 11 && !empty($thumbnail)) {
$rss2 .= '<div class="article">';
$rss2 .= '<article class="card">';
$rss2 .= '<img src="'. $thumbnail .'" width="'.$width.'px" height="'.$height.'px "alt="' . $item->get_title() . '"class="img">';
$rss2 .= '<h3><a rel="nofollow" target="_blank" href="' . $item->get_permalink() . '">' . $item->get_title() . '</a></h3>';
$rss2 .= '<p>'. $item->get_date() .'</p><p>'. shorten($item->get_description(), 450) . '</p>';
$rss2 .= 'Blog Article from: '.$item->get_feed()->get_title().'';
$rss2 .= '</article></div>';

        $imageCount++;
    } else {
        // Add the heading only once before the first article without images
        if (!$headingAdded) {
            $rss2 .= '</div><div class="section"><h4 class="center">Recent Blog Articles</h4>';
            $headingAdded = true;
        }


        $rss2 .= '<button class="accordion"><h3>' . $item->get_title() . '</h3></button>';
        $rss2 .= '<div class="panel"><p>'. $item->get_date() .'</p>';
        $rss2 .= '<p>'. $item->get_description() . '</p>';
        $rss2 .= 'Article from : '.$item->get_feed()->get_title().'<br>';
        $rss2 .= '<br><a rel="nofollow" target="_blank" href="' . $item->get_permalink() . '"> Read More</a>';        
        $rss2 .= '</div>';
    }
}

$template = file_get_contents('blogsbase.html');
$html = str_replace('<!-- posts here -->', $rss2, $template);
file_put_contents('public/blogs.html', $html);
