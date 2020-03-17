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

    /**
     * Create a vocabulary == WP category
     * 
     * @param string $vid
     * @param string $name
     * @param type $desc
     */
    public static function createVocabulary($vid, $name, $desc) {

        if (empty($vid)) {
            throw new \InvalidArgumentException("vocabulary must be valid");
        }
        if (empty($name)) {
            throw new \InvalidArgumentException("name must be valid");
        }
        //\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent, $max_depth, $load_entities);
        $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
        if (!isset($vocabularies[$vid])) {
            $vocabulary = \Drupal\taxonomy\Entity\Vocabulary::create(array(
                        'vid' => $vid,
                        'name' => $name,
                        'description' => $desc,
            ));
            $vocabulary->save();
        } else {
            // Vocabulary Already exist
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', $vid);
            $vocabulary = $query->execute();
        }
        return $vid;
    }

    public static function createSlug($str, $delimiter = '-') {
        print_log($str);
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    /**
     * Create a term == WP tags
     * 
     * @param type $vid
     * @param type $tid
     * @param type $name
     * @param type $desc
     */
    public static function createTerm($vid, $tid, $name, $desc, $parentTermId = null) {
        print_log($desc);

        if (empty($vid)) {
            throw new InvalidArgumentException("vocabulary must be valid");
        }
        if (empty($tid)) {
            throw new InvalidArgumentException("term must be valid");
        }
        if (empty($name)) {
            throw new InvalidArgumentException("name must be valid");
        }
       

        //With options
        $options = [
            'vid' => $vid,
            'name' => $name,
            'description' => [
                'value' => '<p>' . $desc . '</p>',
                'format' => 'full_html',
            ],
        ];
        if (isset($parentTermId))
            $options['parent'] = array($parentTermId);

        $term = \Drupal\taxonomy\Entity\Term::create($options)->save();


        /* $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, NULL, TRUE);
          print_r($terms);
          $query = \Drupal::entityQuery('taxonomy_term');
          $query->condition('vid', 'parent_' . $created_at);
          $tids = $query->execute();
          print_log($tids); */
    }

    /**
     * Programatically create user with array of parameters 
     * 
     * @param type $params
      $params = array(
      'ID' =>
      'user_login' =>
      'user_pass' =>
      'user_email' =>
      )
     */
    public static function createUser($params) {
        try {
            $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
            $user = \Drupal\user\Entity\User::create();

            $user->uid = $params['ID'];
            $user->setUsername($params['user_login']);
            $user->setPassword($params['user_pass']);
            $user->setEmail($params['user_email']);
            $user->enforceIsNew();
            $user->activate();

            $result = $user->save();

            $ids = \Drupal::entityQuery('user')
                    ->condition('status', 1)
                    ->condition('mail', $params['user_email'])
                    ->execute();

            if (count($ids) > 0) {
                foreach ($ids as $key => $value) {
                    $user = \Drupal\user\Entity\User::load($value);

                    if (!empty($params['description'])) {
                        $user->get('field_biography')->setValue($params['description']);
                    } else {
                        $user->get('field_biography')->setValue("");
                    }
                    $words = explode(' ', $params['display_name']);
                    $last_name = array_pop($words);
                    $first_name = implode(" ", $words);

                    $user->get('field_first_name')->setValue($first_name);
                    $user->get('field_last_name')->setValue($last_name);
                    $user->save();
                }
            } else {
                
            }
        } catch (\Exception $ex) {

            // update when user already existed in Drupal 
            $ids = \Drupal::entityQuery('user')
                    ->condition('status', 1)
                    ->condition('mail', $params['user_email'])
                    ->execute();

            if (count($ids) > 0) {
                foreach ($ids as $key => $value) {
                    $user = \Drupal\user\Entity\User::load($value);

                    if (!empty($params['description'])) {
                        $user->get('field_biography')->setValue($params['description']);
                    } else {
                        $user->get('field_biography')->setValue("");
                    }
                    $words = explode(' ', $params['display_name']);
                    $last_name = array_pop($words);
                    $first_name = implode(" ", $words);

                    $user->get('field_first_name')->setValue($first_name);
                    $user->get('field_last_name')->setValue($last_name);
                    $user->save();
                }
            } else {
                
            }
        }
    }

}
