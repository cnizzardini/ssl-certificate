<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;

class SslCertificate
{
    /** @var array */
    protected $rawCertificateFields = [];

    public static function download()
    {
        return new Downloader();
    }

    public static function createForHostName($url, $timeout = 30)
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl($url, $timeout);

        return $sslCertificate;
    }

    public function __construct(array $rawCertificateFields)
    {
        $this->rawCertificateFields = $rawCertificateFields;
    }

    public function getRawCertificateFields()
    {
        return $this->rawCertificateFields;
    }

    public function getIssuer()
    {
        return $this->rawCertificateFields['issuer']['CN'];
    }

    public function getDomain()
    {
        return $this->rawCertificateFields['subject']['CN'] ? $this->rawCertificateFields['subject']['CN'] : '';
    }

    public function getSignatureAlgorithm()
    {
        return $this->rawCertificateFields['signatureTypeSN'] ? $this->rawCertificateFields['signatureTypeSN'] : '';
    }

    public function getAdditionalDomains()
    {
        $this->rawCertificateFields['extensions']['subjectAltName'] ? $this->rawCertificateFields['extensions']['subjectAltName'] : '';
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName']);

        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    public function validFromDate()
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validFrom_time_t']);
    }

    public function expirationDate()
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
    }

    public function isExpired()
    {
        return $this->expirationDate()->isPast();
    }

    public function isValid(string $url = null)
    {
        if (! Carbon::now()->between($this->validFromDate(), $this->expirationDate())) {
            return false;
        }

        if (! empty($url)) {
            return $this->appliesToUrl($url ? $url : $this->getDomain());
        }

        return true;
    }

    public function isValidUntil($carbon, string $url = null)
    {
        if ($this->expirationDate()->lte($carbon)) {
            return false;
        }

        return $this->isValid($url);
    }

    public function appliesToUrl($url)
    {
        $host = (new Url($url))->getHostName();

        $certificateHosts = array_merge([$this->getDomain()], $this->getAdditionalDomains());

        foreach ($certificateHosts as $certificateHost) {
            if ($host === $certificateHost) {
                return true;
            }

            if ($this->wildcardHostCoversHost($certificateHost, $host)) {
                return true;
            }
        }

        return false;
    }

    protected function wildcardHostCoversHost(string $wildcardHost, string $host)
    {
        if ($host === $wildcardHost) {
            return true;
        }

        if (! starts_with($wildcardHost, '*')) {
            return false;
        }

        $wildcardHostWithoutWildcard = substr($wildcardHost, 2);

        return substr_count($wildcardHost, '.') >= substr_count($host, '.') && ends_with($host, $wildcardHostWithoutWildcard);
    }
}
