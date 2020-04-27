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
                    . '<a data-pinterest-text="' . $addingPhoto->title->value . '" data-tweet-text="Describe how do you feel about " ' . $addingPhoto->title->value . '
                                    data-facebook-share-url="' . $addingPhoto->field_photopage_url->value . '" 
                                    data-twitter-share-url="' . $addingPhoto->field_photopage_url->value . '" 
                                    data-googleplus-share-url="' . $addingPhoto->field_photopage_url->value . '" 
                                    data-pinterest-share-url="' . $addingPhoto->field_photopage_url->value . '"
                                    data-download-url="' . $addingPhoto->field_photo_org_url->value . '"
                                    data-sub-html="#caption-' . $addingPhoto->id() . '"
                                    href="' . $addingPhoto->field_photo_big_url->value . '">
                                     <img class="img-responsive thumbnail animated-image" src="' . $addingPhoto->field_photo_thumb_url->value . '">
                                 </a>'
                    . '</div>', [])
        );
        return $response;
    }

}
