<?php

namespace Drupal\flickr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FlickrApiSettingForm.
 */
class FlickrApiSettingForm extends ConfigFormBase {

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

        
        $config = $this->config('flickr.settings');
        
        $form['flickr-api-key'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('API Key:'),
            '#required' => TRUE,
            '#default_value' =>  $config->get("apikey")
        );
        $form['flickr-secret'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('Secret:'),
            '#required' => TRUE,
            '#default_value' =>  $config->get("secret")
        );
        $form['flickr-frob'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('FROB:'),
            '#required' => TRUE,
            '#default_value' =>  $config->get("frob")
        );
        $form['flickr-username'] = array(
            '#type' => 'textfield',
            '#title' => $this
                    ->t('Flickr account name:'),
            '#required' => TRUE,
            '#default_value' =>  $config->get("username")
        );
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->configFactory->getEditable('flickr.settings')
                ->set('apikey', $form_state->getValues()['flickr-api-key'])
                ->set('secret', $form_state->getValues()['flickr-secret'])
                ->set('frob', $form_state->getValues()['flickr-frob'])
                ->set('username', $form_state->getValues()['flickr-username'])
                ->save();
        
        
        parent::submitForm($form, $form_state);
    }

}
