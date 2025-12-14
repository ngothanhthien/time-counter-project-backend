<?php

namespace App\Infrastructure\Auth;

use App\Domain\Contracts\JwtManagerInterface;
use App\Domain\Data\TokenPayload;
use Lcobucci\JWT\Configuration;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Token\Plain;
use Throwable;
use DateTimeImmutable;

class LcobucciJwtManager implements JwtManagerInterface
{
    protected Configuration $config;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded(config('app.jwt.secret'))
        );

        $this->config->setValidationConstraints(
            new SignedWith($this->config->signer(), $this->config->signingKey()),
            new ValidAt(new SystemClock(new DateTimeZone('UTC')))
        );
    }

    public function generateToken(TokenPayload $payload): string
    {
        $now = new DateTimeImmutable();

        $ttl = config('app.jwt.ttl');

        $builder = $this->config->builder()
            ->issuedBy(config('app.url'))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$ttl} seconds"));

        foreach ($payload->toArray() as $key => $value) {
            $builder->withClaim($key, $value);
        }

        return $builder->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }

    public function parseToken(string $token): ?TokenPayload
    {
        try {
            $parsedToken = $this->config->parser()->parse($token);

            if (! $parsedToken instanceof Plain) {
                return null;
            }

            $constraints = $this->config->validationConstraints();

            if (! $this->config->validator()->validate($parsedToken, ...$constraints)) {
                return null;
            }

            $claims = $parsedToken->claims();

            $id = $claims->get('id');
            $ipAddress = $claims->get('ip_address');

            if ($id === null || $ipAddress === null) {
                return null;
            }

            return TokenPayload::from([
                'id' => (int) $id,
                'ip_address' => (string) $ipAddress,
            ]);

        } catch (Throwable $e) {
            return null;
        }
    }
}
