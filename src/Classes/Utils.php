<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\flickr\Classes;

/**
 * Description of Utils
 *
 * @author kyledeveloper
 */
class Utils {

    //put your code here

    const PAGE = 1;
    const NUMBER_PER_PAGE = 100;
    const FLICKR_CRON_INTERVAL = 86400;
    const CRON_VARIABLE_FLICKR_PHOTO = "flickr_photo_next_sync";

   
    /**
     * Convert Exif data to string
     * @param type $photo_id
     * @return string
     */
    public static function get_photo_exif_content($photo_id) {
        $service = \Drupal::service('flickr.download');
        $result = $service->rest_get_flickr_photo_exif($photo_id);
        $exifs = $result->photo->exif;
        $exif_string = '';
        foreach ($exifs as $exif) {
            $exif_string .= "<strong>" . $exif->label . "</strong> : " . $exif->raw->_content . '<br />';
        }
        return $exif_string;
    }

}
