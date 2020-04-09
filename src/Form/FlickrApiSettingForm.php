<?php

namespace Drupal\flickr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FlickrApiSettingForm.
 */
class FlickrApiSettingForm extends ConfigFormBase {

    private $noUsers = 1;

    public static function getKey() {
        return "flickr.flickrapisetting";
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'flickr.flickrapisetting',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'flickr_api_setting_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['#tree'] = true;

        $config = $this->config('flickr.settings');
        if ($form_state->get('number-of-users') !== null) {
            // after add button work
            $this->noUsers = $form_state->get('number-of-users');
            if (!isset($this->noUsers)) {
                $this->noUsers = 1;
            }
        } else {
            $config = $this->config('flickr.settings');
            if ($config->get('noUsers') !== null) {
                $this->noUsers = $config->get('noUsers');
                $form_state->set('number-of-users', $this->noUsers);
            }
        }
        $form['hidden-noUsers'] = array(
            '#type' => 'hidden',
            '#value' => $this->noUsers
        );

        $form['flickr-api-key'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('API Key:'),
            //'#required' => TRUE,
            '#default_value' => ($config->get("apikey") !== null) ? $config->get("apikey") : \Drupal\flickr\Classes\Utils::DEFAULT_API_KEY
        );
        $form['flickr-secret'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('Secret:'),
            //'#required' => TRUE,
            '#default_value' => ($config->get("secret") !== null) ? $config->get("apikey") : \Drupal\flickr\Classes\Utils::DEFAULT_SECRET
        );
        /* $form['flickr-frob'] = array(
          '#type' => 'textfield',
          '#title' => $this
          ->t('FROB:'),
          //'#required' => TRUE,
          '#default_value' => $config->get("frob")
          ); */


        // need multiple field for user
        $form['flickr-users'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Flickr Users:'),
            '#prefix' => '<div id="flickr-users-wrapper">',
            '#suffix' => '</div>',
        );
        for ($i = 0; $i < $this->noUsers; $i++) {

            $form['flickr-users']['user-' . $i] = array(
                '#type' => 'fieldset',
                '#attributes' => array('style' => 'padding-bottom:20px;')
            );

            $form['flickr-users']['user-' . $i]['account-name'] = array(
                '#type' => 'textfield',
                '#title' => $this
                        ->t('Account name #' . ($i + 1) . ':'),
                //'#required' => TRUE,
                '#default_value' => $config->get("user-" . $i)
            );

            if (isset($config) && $config->get("user-" . $i) !== null) {
                $form['flickr-users']['user-' . $i]['download-flickr-users'] = array(
                    '#type' => 'submit',
                    '#name' => $config->get("user-" . $i) . '-download-user-data',
                    '#value' => $this->t('Download User Data'),
                    '#submit' => array(array($this, 'submitDownloadFlickrUser')),
                    '#description' => 'Add fields: biography, first name, last name'
                );

                $form['flickr-users']['user-' . $i]['download-photos'] = array(
                    '#type' => 'submit',
                    '#name' => $config->get("user-" . $i) . '-download-photo',
                    '#value' => $this->t("Download Photos"),
                    '#submit' => array(array($this, 'submitDownloadPhotos'))
                );
                $form['flickr-users']['user-' . $i]['download-flickr-photoset'] = array(
                    '#type' => 'submit',
                    '#name' => $config->get("user-" . $i) . '-download-photo-set',
                    '#value' => $this->t('Download PhotoSets'),
                    '#submit' => array(array($this, 'submitDownloadPhotoSets'))
                );
                $form['flickr-users']['user-' . $i]['link-photo-photoset'] = array(
                    '#type' => 'submit',
                    '#name' => $config->get("user-" . $i) . '-download-photo-set',
                    '#value' => $this->t('Add photos to PhotoSets'),
                    '#submit' => array(array($this, 'submitDownloadPhotoSets'))
                );
            }
        }

        $form['flickr-users']['add-user'] = array(
            '#type' => 'submit',
            '#value' => $this->t('+ Add'),
            '#submit' => ['::adding'],
            '#ajax' => array(
                'callback' => '::ajaxCallback',
                'wrapper' => 'flickr-users-wrapper',
            ),
        );
        if ($this->noUsers > 1) {
            $form['flickr-users']['remove-user'] = array(
                '#type' => 'submit',
                '#value' => '- Remove',
                '#submit' => ['::removeCallback'],
                '#ajax' => array(
                    'callback' => '::ajaxCallback',
                    'wrapper' => 'flickr-users-wrapper',
                ),
            );
        }

        $form = parent::buildForm($form, $form_state);
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $configFactory = $this->configFactory->getEditable('flickr.settings');
        $configFactory->set('apikey', $form_state->getValues()['flickr-api-key'])
                ->set('secret', $form_state->getValues()['flickr-secret'])
                ->set('frob', $form_state->getValues()['flickr-frob'])
                ->set('noUsers', $form_state->getValues()['hidden-noUsers']);
        ;
        $index = 0;
        foreach ($form_state->getValues()['flickr-users'] as $key => $fuser) {
            if (strpos($key, "user-") !== false && !empty($fuser)) {
                $configFactory->set('user-' . $index, $fuser['account-name']);
                $index++;
            }
        }
        $configFactory->save();
        parent::submitForm($form, $form_state);
    }

    public function adding(array &$form, FormStateInterface $form_state) {
        $this->noUsers = $form_state->get('number-of-users');
        $this->noUsers++;
        $form_state->set('number-of-users', $this->noUsers);
        $form_state->setRebuild();
    }

    /**
     * {@inheritdoc}
     */
    public function ajaxCallback(array &$form, FormStateInterface $form_state) {
        return $form['flickr-users'];
    }

    /**
     * {@inheritdoc}
     */
    public function removeCallback(array &$form, FormStateInterface $form_state) {
        $this->noUsers = $form_state->get('number-of-users');
        if ($this->noUsers > 1) {
            $configFactory = $this->configFactory->getEditable('flickr.settings');
            $configFactory->set('user-' . ($this->noUsers - 1), '');
            $configFactory->save();
            $this->noUsers--;
            $form_state->set('number-of-users', $this->noUsers);
        }

        $form_state->setRebuild();
    }

    /**
     * Download Flickr user and create Drupal User
     * 
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitDownloadFlickrUser(array &$form, FormStateInterface $form_state) {
        $parts = explode('-', $form_state->getTriggeringElement()['#name']);
        $user_id = $parts[0];

        $config = $this->config('flickr.settings');
        $operations = array();

        array_push($operations, array('\Drupal\flickr\Form\FlickrAPISettingForm::callbackDownloadFlickrUserOperation', array($user_id)));

        \Drupal\flickr\Classes\BatchOp::start(
                "Creating Users based from Registered Flickr Users",
                "Connecting ...",
                "Updating ....",
                "Update unsuccessfully",
                $operations,
                '\Drupal\yublog_migration\Form\MigrationForm::callbackOperationEnd'
        );
    }

    public function callbackDownloadFlickrUserOperation($flickrUserID, &$context) {
        $service = \Drupal::service('flickr.download');
        $result = $service->rest_get_flickr_user($flickrUserID);

        $params = array(
            'ID' => time(),
            'user_login' => $result->person->username->_content,
            'user_pass' => base64_encode('0o9i8u7y'),
            'user_email' => $result->person->id . '@photo.kylehuynh.com',
            'display-name' => $result->person->username->_content
        );

        $nids = \Drupal\flickr\Classes\Utils::createUser($params, \Drupal\flickr\Classes\Utils::getRole()['id']);

        foreach ($nids as $nid) {
            // CREATE node - type Photo user and link Drupal User ID
            $user = \Drupal\flickr\Classes\Utils::getUserByFlickrID($result->person->id);
            if ($user !== FALSE) {

                $found_ids = \Drupal::entityQuery('node')
                        ->condition('type', 'flickr_user')
                        ->condition('field_user_id', $result->person->id)
                        ->execute();

                if (count($found_ids) == 0) {
                    \Drupal\node\Entity\Node::create(array(
                        'type' => 'flickr_user',
                        'field_count' => $result->person->photos->count->_content,
                        'field_description' => $result->person->description->_content,
                        'field_first_date' => $result->person->photos->firstdate->_content,
                        'field_first_day_taken' => $result->person->photos->firstdatetaken->_content,
                        'field_iconfarm' => $result->person->iconfam,
                        'field_iconserver' => $result->person->iconserver,
                        'field_mobileurl' => $result->person->mobileurl->_content,
                        'field_nsid' => $result->person->nsid,
                        'field_photosurl' => $result->person->photosurl->_content,
                        'field_profileurl' => $result->person->profileurl->_content,
                        'field_username' => $result->person->username->_content,
                        'field_user_id' => $result->person->id,
                        'title' => $result->person->username->_content
                    ))->save();
                } else {
                    foreach ($found_ids as $key => $value) {
                        $node = \Drupal::entityTypeManager()->getStorage('node')->load($value);
                        $node->set('field_count', $result->person->photos->count->_content);
                        $node->set('field_description', $result->person->description->_content);
                        $node->set('field_first_date', $result->person->photos->firstdate->_content);
                        $node->set('field_first_day_taken', $result->person->photos->firstdatetaken->_content);
                        $node->set('field_iconfarm', $result->person->iconfam);
                        $node->set('field_iconserver', $result->person->iconserver);
                        $node->set('field_mobileurl', $result->person->mobileurl->_content);
                        $node->set('field_nsid', $result->person->nsid);
                        $node->set('field_photosurl', $result->person->photosurl->_content);
                        $node->set('field_profileurl', $result->person->profileurl->_content);
                        $node->set('field_username', $result->person->username->_content);
                        $node->set('field_user_id', $result->person->id);
                        $node->set('title', $result->person->username->_content);
                        $node->setOwner($user);
                        $node->save();
                    }
                }
            }
        }
    }

    /**
     * Submit form handler download photo sets
     * 
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitDownloadPhotoSets(array &$form, FormStateInterface $form_state) {
        // Create vocabulary as photoset
        $vocabulary = \Drupal\flickr\Classes\Utils::createVocabulary("photosets", "Photo Sets", "Album");

        $parts = explode('-', $form_state->getTriggeringElement()['#name']);
        $user_id = $parts[0];

        // Create photoset as term under vocabulary photoset
        $service = \Drupal::service('flickr.download');
        $result = $service->rest_get_flickr_photo_set($service, $user_id, 1);
        $operations = array();
        foreach ($result->photosets->photoset as $photoset) {
            array_push($operations, array('\Drupal\flickr\Form\FlickrAPISettingForm::callbackDownloadPhotoSetOperation', array(['vocal' => $vocabulary, 'set' => $photoset])));
        }

        \Drupal\flickr\Classes\BatchOp::start(
                "Pull Photoset and create vocabularies",
                "Connecting ...",
                "Updating ....",
                "Update unsuccessfully",
                $operations,
                '\Drupal\flickr\Form\FlickrAPISettingForm::callbackOperationEnd');
    }

    public function callbackDownloadPhotoSetOperation($data, &$context) {
        $newTag = \Drupal\flickr\Classes\Utils::createTerm(
                        $data['vocal'],
                        $data['set']->id,
                        $data['set']->title->_content,
                        $data['set']->description->_content);

        // CREATE node - type Photo Album and link primary photo and term iD
        $owner = \Drupal\flickr\Classes\Utils::getUserByFlickrID($data['set']->owner);
        if ($owner !== FALSE) {

            $found_ids = \Drupal::entityQuery('node')
                    ->condition('type', 'flickr_album')
                    ->condition('field_photoset_id', $data['set']->id)
                    ->execute();

            if (count($found_ids) == 0) {
                \Drupal\node\Entity\Node::create(array(
                    'type' => 'flickr_album',
                    'title' => "Photoset: ". $data['set']->title->_content,
                    'body' => $data['set']->description->_content,
                    'uid' => $owner->id(),
                    'field_date_uploaded' => $data['set']->date_create,
                    'field_date_last_update' => $data['set']->date_update,
                    'field_count' => $data['set']->count_photos,
                    'field_farm' => $data['set']->farm,
                    'field_photoset_id' => $data['set']->id,
                    'field_photo_id' => $data['set']->primary,
                    'field_secret' => $data['set']->secret,
                    'field_server' => $data['set']->server,
                    'field_term_id' => $newTag
                ))->save();
            } else {

                foreach ($found_ids as $key => $value) {
                    $node = \Drupal::entityTypeManager()->getStorage('node')->load($value);
                    $node->set('title', "Photoset: ". $data['set']->title->_content);
                    $node->set('body', $data['set']->description->_content);
                    $node->set('field_date_uploaded', $data['set']->date_create);
                    $node->set('field_date_last_update', $data['set']->date_update);
                    $node->set('field_count', $data['set']->count_photos);
                    $node->set('field_farm', $data['set']->farm);
                    $node->set('field_photoset_id', $data['set']->id);
                    $node->set('field_photo_id', $data['set']->primary);
                    $node->set('field_secret', $data['set']->secret);
                    $node->set('field_server', $data['set']->server);
                    $node->set('field_term_id', $newTag);
                    $node->setOwner($owner);
                    $node->save();
                }
            }
        }

        // TODO: Download photos in the photoset and tag them 
        $service = \Drupal::service('flickr.download');
        $result = $service->rest_get_flickr_photos_in_set($service, $data['set']->id);
        //print_log($result);
        foreach ($result->photoset->photo as $photo) {
            $query = \Drupal::entityQuery('node')
                    ->condition('type', 'flickr_photo')
                    ->condition('status', 1)
                    ->condition('field_photo_id.value', $photo->id);

            $nids = $query->execute();
            print_log($nids);

            foreach ($nids as $key => $value) {
                print_log($value);
                $nodePhoto = \Drupal::entityTypeManager()->getStorage('node')->load($value);
                print_log($nodePhoto);
                $nodePhoto->field_tags->appendItem(['target_id' => $newTag]);
                $nodePhoto->save();
            }
        }
    }

    public function callbackDownloadPhotoSetOperationEnd($success, $results, $operations) {
        if ($success) {
            $message = \Drupal::translation()
                    ->formatPlural(count($results), 'One Photosets synced.', '@count PhotoSets synced.');
        } else {
            $message = t('Sync processes finished with an error.');
        }
        drupal_set_message($message);

        // Providing data for the redirected page is done through $_SESSION.
        foreach ($results as $key => $value) {
            $items[] = t('Synced applicant %sisid.', array(
                '%sisid' => $key,
            ));
        }
        $_SESSION['my_batch_results'] = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function submitDownloadPhotos(array &$form, FormStateInterface $form_state) {

        $parts = explode('-', $form_state->getTriggeringElement()['#name']);
        $user_id = $parts[0];

        $page = 1;
        $service = \Drupal::service('flickr.download');
        $result = $service->rest_get_flickr_photos($service, $user_id, $page);

        // get total existed photos on Flickr server 
        $total_photos = $result->photos->total;

        // get total pages of photos
        $total_pages = $result->photos->pages;

        $operations = array();
        while ($page <= $total_pages) {
            $result = $service->rest_get_flickr_photos($service, $user_id, $page);

            $fphotos = $result->photos->photo;
            // each page loop 
            foreach ($fphotos as $photo) {
                array_push($operations, array('\Drupal\flickr\Form\FlickrApiSettingForm::callbackDownloadPhotoOperation', array($photo)));
            }
            $page ++;
        }
        \Drupal\flickr\Classes\BatchOp::start(
                "Download Photos Data from Flickr", "Connecting ...", "Download ....", "Download unsuccessfully", $operations, '\Drupal\flickr\Form\FlickrApiSettingForm::callbackDownloadPhotoOperationEnd'
        );
    }

    /**
     * Download Photo Operation callback
     * 
     * @param type $photo
     * @param type $context
     */
    public function callbackDownloadPhotoOperation($photo, &$context) {
        $photo_exif = \Drupal\flickr\Classes\Utils::get_photo_exif_content($photo->id);
        $service = \Drupal::service('flickr.download');
        $photo_info = $service->rest_get_flickr_photo_info($photo->id);

        $found_ids = \Drupal::entityQuery('node')
                ->condition('type', 'flickr_photo')
                ->condition('field_photo_id', $photo->id)
                ->execute();

        $photo_node = $photo_info->photo;
        $owner = user_load_by_mail($photo_node->owner->nsid . "@photo.kylehuynh.com");
        //print_log("Download and insert in page " . $page);
        $thumbnail = "http://farm" . $photo_node->farm . ".staticflickr.com/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_z.jpg";
        $bigphoto = "http://farm" . $photo_node->farm . ".staticflickr.com/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_b.jpg";
        //$bigphoto = "http://c2.staticflickr.com/".$photo_node->farm ."/" . $photo_node->server . "/" . $photo_node->id . "_" . $photo_node->secret . "_t.jpg";
        $orig_photo = "https://www.flickr.com/photos/" . $photo_node->owner->nsid . "/" . $photo_node->id . "/sizes/o/";

        if (count($found_ids) == 0) {
            \Drupal\node\Entity\Node::create(array(
                'type' => 'flickr_photo',
                'title' => ($photo_node->title->_content != null) ? $photo_node->title->_content : " ",
                'field_photo_description' => $photo_node->description->_content,
                'field_photo_id' => $photo->id,
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
                'uid' => $owner->id()
            ))->save();
        } else {
            foreach ($found_ids as $key => $value) {
                $node = \Drupal::entityTypeManager()->getStorage('node')->load($value);
                //$node->set('title', ($photo_node->title->_content != null) ? $photo_node->title->_content : " ");
                //$node->set('field_photo_description', $photo_node->description->_content);
                $node->set('field_photo_id', $photo->id);
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
                $node->setOwner($owner);
                $node->save();
            }
        }
    }

    public function callbackDownloadPhotoOperationEnd($success, $results, $operations) {
        if ($success) {
            $message = \Drupal::translation()
                    ->formatPlural(count($results), 'One Photo synced.', '@count Photos synced.');
        } else {
            $message = t('Sync processes finished with an error.');
        }
        drupal_set_message($message);

        // Providing data for the redirected page is done through $_SESSION.
        foreach ($results as $key => $value) {
            $items[] = t('Synced applicant %sisid.', array(
                '%sisid' => $key,
            ));
        }
        $_SESSION['my_batch_results'] = $items;
    }

}
