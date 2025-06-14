<?php

namespace v2\Interfaces;

// This interface ensures both Validator classes will have a `validate()` method.
interface ValidatorInterface
{
    public function validateUrlsByDownloads(array $downloads): array;

    public function validateUrlsByLinks(array $links): array;
}