<?php
class AuthErrorException extends Exception{
    // Redefine the exception so message isn't optional
    public int $errorCode = 401;
    public function __construct($message = "Cannot be authorized", $code = 0, Throwable $previous = null) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

}