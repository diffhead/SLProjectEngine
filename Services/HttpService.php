<?php namespace Services;

use DateTime;

use Web\HttpHeader;
use Web\HttpRequest;
use Web\HttpResponse;
use Web\HttpCookie;

use Factories\Web\HttpHeaderFactory;
use Factories\Web\HttpCookieFactory;

class HttpService
{
    public static function isHeader(mixed $isHeader): bool
    {
        return $isHeaders instanceof HttpHeader;
    }

    public static function isCookie(mixed $isCookie): bool
    {
        return $isCookes instanceof HttpCookie;
    }

    public static function cookieToSetCookieHeader(HttpCookie $cookie): HttpHeader
    {
        $name = 'Set-Cookie';

        $value = '';
        $value .= $cookie->getName() . '=' . $cookie->getValue() . '; ';
        $value .= 'Expires=' . $cookie->getExpiresRFC() . '; ';
        $value .= $cookie->getSecure() ? 'Secure; ' : '';
        $value .= $cookie->getHttpOnly() ? 'HttpOnly' : '';

        return HttpHeaderFactory::get($name, $value, false);
    }

    public static function getHeadersFromResponseHead(string $responseHead): array
    {
        $headers = [];
        $headLines = StringService::explode($responseHead);

        foreach ( $headLines as $line ) {
            if ( StringService::strPosition($line, ': ') !== -1 ) {
                $headerParts = StringService::explode($line, ': ');
                $headersArray[] = HttpHeaderFactory::get($headerParts[0], $headerParts[1]);
            }
        }

        return $headersArray;
    }

    public static function getCookieFromSetCookieHeader(HttpHeader $header): HttpCookie
    {
        $headerParts = StringService::explode($header->getValue(), '; ');

        $cookieName = '';
        $cookieValue = '';
        $cookieExpires = time() + 3600 * 24;
        $cookiePath = '';
        $cookieDomain = '';
        $cookieSecure = false;
        $cookieHttpOnly = false;

        foreach ( $headerParts as $part ) {
            $equalSymbolPos = StringService::strPosition($part, '=');

            $paramName = StringService::trim(StringService::subString($part, 0, $equalSymbolPos));
            $paramValue = StringService::trim(StringService::subString($part, $equalSymbolPos + 1));

            switch ( $paramName ) {
                case 'expires':
                    $dateTime = new DateTime($paramValue);
                    $cookieExpires = $dateTime->getTimestamp();
                break;

                case 'domain':
                    $cookieDomain = $paramValue;
                break;

                case 'path':
                    $cookiePath = $paramValue;
                break;

                case 'Secure':
                    $cookieSecure = true;
                break;

                case 'HttpOnly':
                    $cookieHttpOnly = true;
                break;

                default:
                    $cookieName = $paramName;
                    $cookieValue = $paramValue;
                break;
            }
        }

        return HttpCookieFactory::get(
            $cookieName, $cookieValue, $cookieExpires, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly
        );
    }

    public static function setResponseCode(int $code): void
    {
        http_response_code($code);
    }

    public static function setResponseCookie(HttpCookie $cookie): void
    {
        setcookie(
            $cookie->getName(), $cookie->getValue(), $cookie->getExpires(), 
            $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(),
            $cookie->getHttpOnly()
        );
    }

    public static function setResponseHeader(HttpHeader $header): void
    {
        header($header->getRaw(), $header->isForReplace());
    }
}
