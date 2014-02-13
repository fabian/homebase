<?php

namespace Homebase\Service;

class RedirectUri
{
    /**
     * @param string $uri
     * @param array $params
     * @param string $delimeter
     * @return string
     */
    public static function create($uri, $params = array(), $delimeter = '?')
    {
        if (strstr($uri, $delimeter) === false) {
            $delimiter = $delimeter;
        } else {
            $delimiter = '&';
        }
        $uri .= $delimiter;

        return $uri . http_build_query($params);
    }
}
