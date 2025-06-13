<?php

namespace Brucelwayne\Subscribe\Objects;

class RecipientData
{
    public string $email;
    public array $variables;

    public function __construct(string $email, array $variables = [])
    {
        $this->email = $email;
        $this->variables = $variables;
    }
}