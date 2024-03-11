<?php

/**
 * Make array from message string
 * 
 * @param $message string|array 
 * @return array
 */
if ( ! function_exists('error_json') ) 
{  
  function error_json($message)
  {
    $errors = ['error' => true];
    if( $message ) {
      $errors['messages'] = $message;
    }

    return $errors;
  }
}


/**
 * Make array from message string
 * 
 * @param $message string|array 
 * @return array
 */
if ( ! function_exists('error_validation_json') ) 
{  
  function error_validation_json($message)
  {
    $errors = ['error' => true, 'validated' => false];
    if( $message ) {
      $errors['messages'] = $message;
    }

    return $errors;
  }
}