<?php
declare(strict_types=1);

namespace FT\HealthCheckBundle\Service;

use ErrorException;

/**
 * Handles PHP5 and PHP7 core errors that cannot be handled by the regular try catch mechanism.
 */
class HealthCheckExceptionHandlerService {

    /** 
     * Sets up an exception handler pre script execution
     */
    function setupExceptionHandler(){
        $this->previousErrorHandle = set_error_handler(function($errno, $errstr, $errfile, $errline ){
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        }); 
    }
}