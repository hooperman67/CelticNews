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

// Initialize SimplePie instance
$blogs_feed = new \SimplePie();
$blogs_feed->set_feed_url($blogs_rss);
$blogs_feed->set_item_limit(15);
$blogs_feed->strip_htmltags(array_merge($blogs_feed->strip_htmltags, array('p', 'em')));
$blogs_feed->init();

function shorten($string, $length) {
    $suffix = '&hellip;';
    $short_desc = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));
    $desc = trim(substr($short_desc, 0, $length));
    $lastchar = substr($desc, -1, 1);
    if ($lastchar == '.' || $lastchar == '!' || $lastchar == '?') $suffix='';
    return $desc . $suffix;
}

function getThumbnail($item) {
    $content = $item->get_content();
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($content);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $srcset = $xpath->evaluate("string(//img/@srcset)");

    if (!empty($srcset)) {
        $sources = explode(',', $srcset);
        foreach ($sources as $source) {
            $parts = explode(' ', trim($source));
            $url = trim($parts[0]);
            $width = (int)trim($parts[1], 'w');
            if ($width <= 600) {
                return $url;
            }
        }
    }

    if (null !== ($enclosure = $item->get_enclosure(0))) {
        if ($enclosure->get_thumbnail()) {
            return $enclosure->get_thumbnail();
        } elseif ($enclosure->get_link()) {
            return str_replace("_m.jpg", "_s.jpg", $enclosure->get_link());
        }
    }

    return 'images/craic.webp';
}

function processBlogItems($blogs_feed) {
    $rss2 = '';
    $imageCount = 0;
    $headingAdded = false;

    foreach ($blogs_feed->get_items(0, 20) as $item) {
        $thumbnail = getThumbnail($item);
        list($width, $height) = getimagesize($thumbnail);
        $width = intval($width);
        $height = intval($height);

        if ($imageCount < 11 && !empty($thumbnail)) {
            $rss2 .= '<div class="article">';
            $rss2 .= '<article class="card">';
            $rss2 .= '<img src="'. $thumbnail .'" width="'.$width.'px" height="'.$height.'px" alt="' . $item->get_title() . '" class="img">';
            $rss2 .= '<h3><a rel="nofollow" target="_blank" href="' . $item->get_permalink() . '">' . $item->get_title() . '</a></h3>';
            $rss2 .= '<p>'. $item->get_date() .'</p><p>'. shorten($item->get_description(), 450) . '</p>';
            $rss2 .= 'Blog Article from: '.$item->get_feed()->get_title().'';
            $rss2 .= '</article></div>';
            $imageCount++;
        } else {
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

    return $rss2;
}

$rss2 = processBlogItems($blogs_feed);

$template = file_get_contents('blogsbase.html');
$html = str_replace('<!-- posts here -->', $rss2, $template);
file_put_contents('public/blogs.html', $html);
?>
