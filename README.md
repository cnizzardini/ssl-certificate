This is a PHP 5.6 fork of the wonderful spatie/ssl-certificate library for validating, analyzing, and getting SSL certificate meta data.

I needed a 5.6 port for a project I was working on. If you don't need a 5.6 compatible version, then please use the standard library instead. https://github.com/spatie/ssl-certificate

To install via composer requre: 

"cnizzardini/ssl-certificate": "dev-master"

And add the repository:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/cnizzardini/ssl-certificate.git"
        }
    ]

Use at your own risk, I'll stop supporting this once I am able to upgrade the project I am working in to PHP 7.
