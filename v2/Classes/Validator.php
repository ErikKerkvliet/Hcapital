<?php

namespace v2\Classes;

use v2\Classes\ValidatorLocal;
use v2\Classes\ValidatorRemote;

class Validator
{
    public static function getValidator()
    {
        return AdminCheck::checkForLocal() ? new ValidatorLocal() : new ValidatorRemote();
    }
}