<?php
/**
 * @file
 * Contains App\Service\Alert.
 */

namespace App\Service;

class Alert implements AlertInterface
{
    /**
     * @var string
     */
    protected $message;

    public function __construct($message)
    {
        $this->message = (string) $message;
    }

    public function __toString()
    {
        return $this->message;
    }
}