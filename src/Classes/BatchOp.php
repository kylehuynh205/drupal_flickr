<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\flickr\Classes;

/**
 * Description of BatchOp
 *
 * @author huynhk
 */
class BatchOp {

    /**
     * Start a Batch Op
     * @param type $title
     * @param type $init_messagem
     * @param type $progress_message
     * @param type $error_message
     * @param type $operations = array(
      array('my_function_1',array($account->id(),'story')),
      array('my_function_2', array()),
      )
     * @param type $callback_finished
     * @param type $path_to_file_containing_myfunctions
     * @throws ErrorException
     */
    public static function start($title, $init_messagem, $progress_message, $error_message, $operations, $callback_finished = null, $path_to_file_containing_myfunctions = null) {
        if (!is_array($operations)) {
            throw new \ErrorException("Operation must be an array");
        }
        $batch = array(
            'title' => t($title),
            'init_message' => t($init_messagem),
            'progress_message' => t($progress_message),
            'error_message' => t($error_message),
            'operations' => $operations,
        );
        if (isset($path_to_file_containing_myfunctions)) {
            $batch['file'] = $path_to_file_containing_myfunctions;
        }

        if (isset($callback_finished)) {
            $batch['finished'] = $callback_finished;
        }

        batch_set($batch);
    }

    /**
     * Call back when Batch ops ends
     * 
     * @param type $success
     * @param type $results
     * @param type $operations
     */
    public static function endBatchWithOps($success, $results, $operations) {
        if ($success) {
            $message = \Drupal::translation()
                    ->formatPlural(count($results), 'One post processed.', '@count posts processed.');
        } else {
            $message = t('Finished with an error.');
        }
        \Drupal::messenger()->addMessage($message);

        // Providing data for the redirected page is done through $_SESSION.
        foreach ($results as $result) {
            $items[] = t('Loaded node %title.', array(
                '%title' => $result,
            ));
        }
        $_SESSION['my_batch_results'] = $items;
    }

}
