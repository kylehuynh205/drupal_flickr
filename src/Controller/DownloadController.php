<?php

namespace Drupal\flickr\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DownloadController.
 */
class DownloadController extends ControllerBase {

    /**
     * Download.
     *
     * @return string
     *   Return Hello string.
     */
    public function download() {
        $service = \Drupal::service('flickr.download');
        $page = 1;
        $result = $service->rest_get_flickr_photos($service, $page);

        \Utils::do_download_all_photos_at_once($service, $result);

        return [
            '#type' => 'markup',
            '#markup' => $this->t('Implement method: download')
        ];
    }

   

    

}
