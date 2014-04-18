<?php
namespace RoundTownWs\Utils;

/**
 * @author Matteo Magni <matteo@magni.me>
 *
 */
class PlaceUtility {

    public function __construct() {

    }

    /**
     * Compare name  off venues
     */
    public function sortByName($a, $b) {
        $a['name'] = strtolower($a['name']);
        $b['name'] = strtolower($b['name']);
        return strcmp($a['name'], $b['name']);
    }

    /**
     *
     */
    public function getImageByGroups($groups, $format = 'normal') {
        switch ($format) {
            case 'thumb':
                $size = '150x150';
                break;
            case 'minithumb':
                $size = '80x80';
                break;
            default:
                $size = 'width960';
                break;
        }
        return $groups[0]['items'][0]['prefix'] . $size . $groups[0]['items'][0]['suffix'];
    }
}
