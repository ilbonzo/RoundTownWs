<?php
namespace RoundTownWs\Utils;

/**
 * @author Matteo Magni <matteo@magni.me>
 *
 */
class ImageUtility {

    public function __construct() {

    }

    /**
     * Compare time of image
     */
    public function sortByTime($a, $b) {
        return $a['time'] < $b['time'];
    }
}
