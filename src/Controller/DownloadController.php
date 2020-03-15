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
       

        return [
            '#type' => 'markup',
            '#markup' => $this->t('Implement method: download')
        ];
    }

}
