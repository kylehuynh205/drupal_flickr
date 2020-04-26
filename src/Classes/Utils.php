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
    const DEFAULT_API_KEY = 'dad4f85b8591e6475b468f0c7e8feb88';
    const DEFAULT_SECRET = '33e5294e113a9c0b';

    public static function getRole() {
        return array('id' => 'flickr_user', 'label' => 'Photographer');
    }

    /**
     * check if the role exist in the system
     * @param type $role_name
     * @return type
     * @throws Exception
     */
    public static function isRoleExisted($role_name) {
        if (!isset($role_name) || empty($role_name)) {
            throw new \Exception("Role name parameter must not NULL");
        }
        return array_key_exists($role_name, \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple());
    }

    /**
     * Create role by default for module by name
     * execute when module installed 
     * 
     * @param type $role_name
     * @throws Exception
     */
    public static function createRole($role_name) {
        if (!isset($role_name) || empty($role_name) && !is_array($role_name)) {
            throw new \Exception("Role name parameter must not NULL");
        }
        $role = \Drupal\user\Entity\Role::create($role_name);
        $role->grantPermission('access toolbar');
        $role->grantPermission('view the administration theme');
        $role->save();
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

        if (empty($vid)) {
            throw new InvalidArgumentException("vocabulary must be valid");
        }
        if (empty($tid)) {
            throw new InvalidArgumentException("term must be valid");
        }
        if (empty($name)) {
            throw new InvalidArgumentException("name must be valid");
        }
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, NULL, TRUE);
        $existed = self::isTermExisted($name, $terms);

        if ($existed === false) {
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
            return $term;
        }
        return $existed;
    }

    /**
     * Check the term existed 
     * 
     * @param string $name
     * @param array $terms
     * @return boolean
     */
    public static function isTermExisted(string $name, array $terms) {
        $flag = false;
        foreach ($terms as $term) {
            if ($term->getName() === $name) {
                $flag = $term->id();
                break;
            }
        }
        return $flag;
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
    public static function createUser($params, $role = null) {
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

                    /* if (!empty($params['description'])) {
                      $user->get('field_biography')->setValue($params['description']);
                      } else {
                      $user->get('field_biography')->setValue("");
                      }
                      $words = explode(' ', $params['display_name']);
                      $last_name = array_pop($words);
                      $first_name = implode(" ", $words);

                      $user->get('field_first_name')->setValue($first_name);
                      $user->get('field_last_name')->setValue($last_name); */

                    // add role
                    if (isset($role) && Utils::isRoleExisted($role)) {
                        $user->addRole($role);
                    }
                    $user->save();
                }
            }
            return $ids;
        } catch (\Exception $ex) {

            // update when user already existed in Drupal 
            $ids = \Drupal::entityQuery('user')
                    ->condition('status', 1)
                    ->condition('mail', $params['user_email'])
                    ->execute();

            if (count($ids) > 0) {
                foreach ($ids as $key => $value) {
                    $user = \Drupal\user\Entity\User::load($value);

                    /* if (!empty($params['description'])) {
                      $user->get('field_biography')->setValue($params['description']);
                      } else {
                      $user->get('field_biography')->setValue("");
                      }
                      $words = explode(' ', $params['display_name']);
                      $last_name = array_pop($words);
                      $first_name = implode(" ", $words);

                      $user->get('field_first_name')->setValue($first_name);
                      $user->get('field_last_name')->setValue($last_name); */

                    // add role
                    if (isset($role) && Utils::isRoleExisted($role)) {
                        $user->addRole($role);
                    }
                    $user->save();
                }
            }
            return $ids;
        }
    }

    /**
     * get query from flickr id
     * @param string $flickr_id
     * @return type
     */
    public static function getUserByFlickrID(string $flickr_id) {
        return user_load_by_mail($flickr_id . '@photo.kylehuynh.com');
    }

    public static function createNodeAlias(\Drupal\node\Entity\Node $node) {
        if ($node->getType() == "flickr_user") {
            $tag = "/photographer/" . $node->field_user_id->value . "/" . self::createSlug($node->title->value);
            if (!\Drupal::service('path.alias_storage')->aliasExists($tag, 'en')) {
                $path = \Drupal::service('path.alias_storage')->save("/node/" . $node->id(), $tag, "en");
            }
        } else if ($node->getType() == "flickr_album") {
            $tag ="/photoset/" . $node->field_photoset_id->value . '/' .  self::createSlug($node->title->value);
            if (!\Drupal::service('path.alias_storage')->aliasExists($tag, 'en')) {
                $path = \Drupal::service('path.alias_storage')->save("/node/" . $node->id(), $tag, "en");
            }
        } else {
            $tag = "/photo/" . $node->field_photo_id->value . "/" . self::createSlug($node->title->value);
            if (!\Drupal::service('path.alias_storage')->aliasExists($tag, 'en')) {
                $path = \Drupal::service('path.alias_storage')->save("/node/" . $node->id(), $tag, "en");
            }
        }
        return $path;
    }

    /**
     * 
     * @param type $tag
     * @return type
     */
    public static function encodeAliasUrl($tag) {
        if (!empty($tag)) {
            if (\Drupal::service('path.alias_storage')->aliasExists($tag, 'en')) {
                //$tag .= $tag . time();
                \Drupal::entityTypeManager()->getStorage('path_alias')->delete([$tag]);
            }
        }
        return $tag;
    }

}
