<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\flickr\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Description of FormAlterAjaxCallback
 *
 * @author kyledeveloper
 */
class FormAlterAjaxCallback {

    //put your code here


    public static function ajaxCallbackLoadSelectedPhoto(array &$form, FormStateInterface $form_state) {

        $photoid = $form_state->getValues()['add-photos-to-photoset'][0]['target_id'];
        print_log("loading " . $photoid );
        $addingPhoto = \Drupal\node\Entity\Node::load($photoid);
        
        $response = array(
            '#markup' => new \Drupal\Component\Render\FormattableMarkup(
                    '<div id="photo-to-added-gallery" class="list-unstyled justified-gallery clearfix" style="height: auto;">'
                        . '<img class="img-responsive thumbnail animated-image" src="' . $addingPhoto->field_photo_thumb_url->value . '" alt="'.$addingPhoto->title->value.'">'
                        
                    . '</div>', [])
        );
        return $response;
    }

}
