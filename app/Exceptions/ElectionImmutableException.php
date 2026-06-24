<?php

namespace App\Exceptions;

use RuntimeException;

class ElectionImmutableException extends RuntimeException
{
    public static function archived(): self
    {
        return new self('This election is archived and cannot be modified.');
    }

    public static function activeElectionExists(): self
    {
        return new self('An active election cycle already exists. Archive it before starting a new one.');
    }

    public static function unarchivedCompletedExists(): self
    {
        return new self('The completed election must be archived before starting a new cycle.');
    }

    public static function concurrentOperationalElection(): self
    {
        return new self('Another election is already in EOI, accreditation, or voting. Finish or archive it first.');
    }
}
