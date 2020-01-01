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
        print_log(buildForm);

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
            '#default_value' => $config->get("apikey")
        );
        $form['flickr-secret'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('Secret:'),
            //'#required' => TRUE,
            '#default_value' => $config->get("secret")
        );
        $form['flickr-frob'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('FROB:'),
            //'#required' => TRUE,
            '#default_value' => $config->get("frob")
        );


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
            );
                    
            $form['flickr-users']['user-' . $i]['account-name'] = array(
                '#type' => 'textfield',
                '#title' => $this
                        ->t('Account name #' . ($i + 1) . ':'),
                //'#required' => TRUE,
                '#default_value' => $config->get("user-" . $i)
            );
            if ($this->noUsers > 1) {
                $form['flickr-users']['user-' . $i]['remove'] = array(
                    '#type' => 'submit',
                    '#value' => 'Remove',
                    '#submit' => ['::removeCallback'],
                    '#ajax' => array(
                        'callback' => '::addCallback',
                        'wrapper' => 'siteCreditsWrapper',
                    ),
                );
            }
        }

        $form['flickr-users']['add_person'] = array(
            '#type' => 'submit',
            '#value' => $this->t('+ Add'),
            '#submit' => ['::adding'],
            '#ajax' => array(
                'callback' => '::addCallback',
                'wrapper' => 'flickr-users-wrapper',
            ),
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $configFactory = $this->configFactory->getEditable('flickr.settings');
        $configFactory->set('apikey', $form_state->getValues()['flickr-api-key'])
                ->set('secret', $form_state->getValues()['flickr-secret'])
                ->set('frob', $form_state->getValues()['flickr-frob'])
                ->set('noUsers', $form_state->getValues()['hidden-noUsers']);
                //->save();
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
    public function addCallback(array &$form, FormStateInterface $form_state) {
        return $form['flickr-users'];
    }

    /**
     * {@inheritdoc}
     */
    public function removeCallback(array &$form, FormStateInterface $form_state) {
        $flickrUsers = $form_state->get('number-of-users');
        if ($flickrUsers > 1) {
            $flickrUsers--;
            $form_state->set('number-of-users', $flickrUsers);
        }

        $form_state->setRebuild();
    }

}
