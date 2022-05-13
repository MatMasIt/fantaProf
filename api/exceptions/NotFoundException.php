<?php
class NotFoundException extends Exception{
    // Redefine the exception so message isn't optional
    public int $errorCode = 404;
    public function __construct($message = "The specified resource was not found", $code = 0, Throwable $previous = null) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

}