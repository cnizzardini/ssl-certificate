<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;

class CouldNotDownloadCertificate extends Exception
{
    public static function hostDoesNotExist(string $hostName)
    {
        return new static("The host named `{$hostName}` does not exist.");
    }

    public static function noCertificateInstalled(string $hostName)
    {
        return new static("Could not find a certificate on  host named `{$hostName}`.");
    }

    public static function unknownError(string $hostName, string $errorMessage)
    {
        return new static("Could not download certificate for host `{$hostName}` because {$errorMessage}");
    }
}
