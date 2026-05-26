<?php
function fixHTag($html) {
    $outputHtml = preg_replace_callback('/<(\/?)(h[3-6])(>|\s.*?>)/s', function($matches) {
        $additional = $matches[3];

        if (empty($matches[1])) {
            if (strpos($additional, 'class="') !== false) {
                $additional = str_replace('class="', 'class="' . $matches[2] . ' ', $additional);
            } else {
                $additional = ' class="' . $matches[2] . '"' . $additional;
            }
        }

        return '<' . $matches[1] . 'div' . $additional;
    }, $html);

    return $outputHtml;
}

function fixOtherTag($html) {
    $outputHtml = preg_replace_callback('/<(\/?)(b|big|em|small|strong|sub|sup)(>|\s.*?>)/s', function($matches) {
        $additional = $matches[3];

        if (empty($matches[1])) {
            if (strpos($additional, 'class="') !== false) {
                $additional = str_replace('class="', 'class="' . $matches[2] . ' ', $additional);
            } else {
                $additional = ' class="' . $matches[2] . '"' . $additional;
            }
        }

        return '<' . $matches[1] . 'span' . $additional;
    }, $html);

    return $outputHtml;
}

function fixOldLink($html) {

    /**
     * Banned links.
     */

    $bannedLinks = [
        //'https://radio-shop.com.ua',
        //'http://radio-shop.com.ua',
    ];

    preg_match_all('/(src|href)=("|\').*?("|\')/s', $html, $hrefs);

    if (!empty($hrefs) && !empty($hrefs[0])) {
        $hrefs = array_unique($hrefs[0]);

        foreach ($bannedLinks as $bannedLink) {
            if (strpos($html, $bannedLink) !== false) {
                foreach ($hrefs as $href) {
                    if (strpos($href, $bannedLink) !== false) {
                        $html = str_replace($href, $href . ' rel="noindex, nofollow"', $html);
                    }
                }
            }
        }
    }

    return $html;
}
