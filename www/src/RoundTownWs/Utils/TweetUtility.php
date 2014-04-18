<?php
namespace RoundTownWs\Utils;

/**
 * @author Matteo Magni <matteo@magni.me>
 *
 */
class TweetUtility {

    public function __construct() {

    }

    /**
     * Compare id off tweet
     */
    public function sortById($a, $b) {
        return $a['id'] < $b['id'];
    }
}
