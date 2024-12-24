<?php
namespace App\Http\Exception;

class AuthException extends \Exception
{
    protected $code;
    protected $message;
    protected $status;

    public function __construct($message = null, $code = 401, $previous = null)
    {
        parent::__construct($message ?? 'Unauthorized', $code, $previous);
        $this->status = $code;
    }


    public function getStatus()
    {
        return $this->status;
    }
}