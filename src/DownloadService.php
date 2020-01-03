<?php

namespace Drupal\flickr;

/**
 * Class DownloadService.
 */
class DownloadService {

    /**
     * Constructs a new DownloadService object.
     */
    public function __construct() {
        
    }

    public function rest_get_flickr_user() {
        $config = $this->config('flickr.settings');
        
        $response = \Drupal::httpClient()->get("https://api.flickr.com/services/rest/?method=flickr.people.getInfo&api_key=" . $config->get("apikey") . "&user_id=" . $config->get("user-0") . "&format=json&nojsoncallback=1");
        return json_decode((string) $response->getBody());
    }

    public function rest_get_flickr_photos($service, $page) {
        $config = $this->config('flickr.settings');
        $response = \Drupal::httpClient()->get("https://api.flickr.com/services/rest/?method=flickr.people.getPhotos&api_key=" . $config->get("apikey") . "&user_id=" . $config->get("user-0") . "&per_page=" . \Utils::NUMBER_PER_PAGE . "&page=" . $page . "&extras=date_upload%2Cviews&format=json&nojsoncallback=1&sort=date-posted-desc");
        return json_decode((string) $response->getBody());
    }

    public function rest_get_flickr_photo_info($photo_id) {
        $config = $this->config('flickr.settings');
        $response = \Drupal::httpClient()->get("https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=" . $config->get("apikey") . "&photo_id=" . $photo_id . "&format=json&nojsoncallback=1");
        return json_decode((string) $response->getBody());
    }

    public function rest_get_flickr_photo_exif($photo_id) {
        $config = $this->config('flickr.settings');
        $response = \Drupal::httpClient()->get("https://api.flickr.com/services/rest/?method=flickr.photos.getExif&api_key=" . $config->get("apikey") . "&photo_id=" . $photo_id . "&format=json&nojsoncallback=1");
        return json_decode((string) $response->getBody());
    }

}