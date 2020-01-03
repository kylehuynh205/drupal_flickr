<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
     * Download all photos at once.
     * 
     * @param type $result Result of flickr.people.getPhotos web service 
     */
    public static function do_download_all_photos_at_once($service, $result) {
        // get total existed photos on Flickr server 
        $total_photos = $result->photos->total;

        // get total pages of photos
        $total_pages = $result->photos->pages;

        while ($page <= $total_pages) {
            $result = $service->rest_get_flickr_photos($service, $page);
            self::download_single_page_photos($result, $page);
            $page ++;
        }
    }

    /**
     * Download a single page of photos from Flickr service.
     * 
     * @param type $result
     * @param type $page
     */
    public static function download_single_page_photos($result, $page = -1) {

        $service = \Drupal::service('flickr.download');
        // get first page of photos
        $fphotos = $result->photos->photo;
        // each page loop 
        foreach ($fphotos as $photo) {
            $photo_exif = self::get_photo_exif_content($photo->id);

            $photo_info = $service->rest_get_flickr_photo_info($photo->id);

            $found_ids = \Drupal::entityQuery('node')
                    ->condition('type', 'flickr_photo')
                    ->condition('field_photo_id', $photo->id)
                    ->execute();

            $photo_node = $photo_info->photo;
            if (count($found_ids) == 0) {
                //print_log("Download and insert in page " . $page);
                $thumbnail = "http://farm" . $photo_node->farm . ".staticflickr.com/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_z.jpg";
                $bigphoto = "http://farm" . $photo_node->farm . ".staticflickr.com/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_b.jpg";
                //$bigphoto = "http://c2.staticflickr.com/".$photo_node->farm ."/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_t.jpg";
                $orig_photo = "https://www.flickr.com/photos/" . $photo_node->owner->nsid . "/" . $photo_node->id . "/sizes/o/";

                \Drupal\node\Entity\Node::create(array(
                    'type' => 'flickr_photo',
                    'title' => ($photo_node->title->_content != null) ? $photo_node->title->_content : " ",
                    'field_photo_description' => $photo_node->description->_content,
                    'field_photo_id' => $photo_node->id,
                    'field_secret' => $photo_node->secret,
                    'field_server' => $photo_node->server,
                    'field_farm' => $photo_node->farm,
                    'field_date_uploaded' => $photo_node->dateuploaded,
                    'field_owner' => $photo_node->owner->nsid,
                    'field_ispublic' => $photo_node->visibility->ispublic,
                    'field_isfriend' => $photo_node->visibility->isfriend,
                    'field_isfamily' => $photo_node->visibility->isfamily,
                    'field_date_last_update' => $photo_node->dates->lastupdate,
                    'field_date_taken' => $photo_node->dates->taken,
                    'field_date_takengranularity' => $photo_node->dates->takengranularity,
                    'field_date_taken_unknown' => $photo_node->dates->takenunknown,
                    'field_views' => $photo_node->views,
                    'field_photopage_url' => $photo_node->urls->url[0]->_content,
                    'field_photo_big_url' => $bigphoto,
                    'field_photo_thumb_url' => $thumbnail,
                    'field_photo_org_url' => $orig_photo,
                    'field_photo_exif' => $photo_exif,
                ))->save();
            } else {
                foreach ($found_ids as $key => $value) {
                    $node = \Drupal::entityTypeManager()->getStorage('node')->load($value);
                    //$node->set('title', ($photo_node->title->_content != null) ? $photo_node->title->_content : " ");
                    //$node->set('field_photo_description', $photo_node->description->_content);
                    $node->set('field_photo_id', $photo_node->id);
                    $node->set('field_secret', $photo_node->secret);
                    $node->set('field_server', $photo_node->server);
                    $node->set('field_farm', $photo_node->farm);
                    $node->set('field_date_uploaded', $photo_node->dateuploaded);
                    $node->set('field_owner', $photo_node->owner->nsid);
                    $node->set('field_ispublic', $photo_node->visibility->ispublic);
                    $node->set('field_isfriend', $photo_node->visibility->isfriend);
                    $node->set('field_isfamily', $photo_node->visibility->isfamily);
                    $node->set('field_date_last_update', $photo_node->dates->lastupdate);
                    $node->set('field_date_taken', $photo_node->dates->taken);
                    $node->set('field_date_takengranularity', $photo_node->dates->takengranularity);
                    $node->set('field_date_taken_unknown', $photo_node->dates->takenunknown);
                    $node->set('field_views', $photo_node->views);
                    $node->set('field_photopage_url', $photo_node->urls->url[0]->_content);
                    $node->set('field_photo_big_url', $bigphoto);
                    $node->set('field_photo_thumb_url', $thumbnail);
                    $node->set('field_photo_org_url', $orig_photo);
                    $node->set('field_photo_exif', " UPDATE" . $photo_exif);
                    $node->save();
                }
            }
        }
    }

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
