<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\ValueObject;

use Symfony\Component\HttpFoundation\Request;

class ClientIp
{
    private string $value;

    public static function createFromRequest(Request $request): self
    {
        $httpXForwardedFor = self::parseClientIpFromXForwardedFor(
            $request->server->get('HTTP_X_FORWARDED_FOR', '')
        );

        if (!empty($httpXForwardedFor->getValue())) {
            return $httpXForwardedFor;
        }

        // on vagrant environment there is no proxy so HTTP_X_FORWARDED_FOR is not set => we must check REMOTE_ADDR
        $remoteAddr = $request->server->get('REMOTE_ADDR', '');

        return new self($remoteAddr);
    }

    /**
     * Parse IP of the original client from the value of X-Forwarded-For header.
     *
     * The value could consist of more comma-separated IPs, the left-most being the original client,
     * and each successive proxy that passed the request adding the IP address where it received the request from.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For#syntax
     */
    public static function parseClientIpFromXForwardedFor(string $xForwardedFor): self
    {
        $ips = array_map('trim', explode(',', $xForwardedFor));

        return new self($ips[0] ?? '');
    }

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function empty(): self
    {
        return new self('');
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
