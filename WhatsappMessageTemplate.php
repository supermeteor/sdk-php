<?php

namespace Supermeteor;

class WhatsappTemplateMessage
{
    public $name;

    public $languageCode;

    public $parameters;

    /**
     * Client constructor.
     * @param $secretKey
     * @param bool $sandbox
     */
    public function __construct($name = null, $languageCode = null, $parameters = [])
    {
        $this->name = $name;
        $this->languageCode = $languageCode;
        $this->parameters = $parameters;
    }
}
