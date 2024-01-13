<?php


namespace Shorter\Backend\Utils;


use Shorter\Backend\Utils\Exceptions\JwtExpireException;
use Shorter\Backend\Utils\Exceptions\JwtMalformException;

class JWT
{

    public function __construct(private array $header = [], private array $payload = [], private bool $canExpire = false)
    {

        if ($this->canExpire) {

            $this->payload["expire_at"] = time() + $_ENV["JWT_TTL"];

        }

    }

    public function __toString()
    {

        return $this->pack();

    }

    /**
     * @param string $token jwt
     * @param bool $deepUnpacking 0 - will provide data header & payload as base64 1 - will provide header & payload as array. | The signature always remains in hashed format.
     * @return array
     */
    public static function unpack(string $token, bool $deepUnpacking = false): array
    {

        $explodedTokenByDot = explode(".", $token);

        if (count($explodedTokenByDot) != 3) throw new JwtMalformException();

        $headerAsBase64 = $explodedTokenByDot[0];
        $payloadAsBase64 = $explodedTokenByDot[1];

        $signature = base64_decode($explodedTokenByDot[2]);

        if ($deepUnpacking) {

            $headerAsJson = base64_decode($headerAsBase64);
            $payloadAsJson = base64_decode($payloadAsBase64);

            $header = json_decode($headerAsJson, 1);
            $payload = json_decode($payloadAsJson, 1);

        }

        return [
            $header ?? $headerAsBase64,
            $payload ?? $payloadAsBase64,
            $signature
        ];

    }

    /**
     * The verification method is described at https://jwt.io.
     * @return string
     */
    public static function verify(string $token, array $headerValidation): JWT
    {

        $UnpackedToken = self::unpack($token, true);
        $JWT = new JWT($UnpackedToken[0], $UnpackedToken[1], isset($UnpackedToken[1]["expire_at"]));

        $providedSignature = $UnpackedToken[2];
        $preparedToken = $JWT->prepareToken();

        $preparedTokenSalted = $preparedToken . $_ENV["HASH_SALT"];

        if (isset($UnpackedToken[1]["expire_at"])) {

            if (time() >= $UnpackedToken[1]["expire_at"]) {

                throw new JwtExpireException();

            }

        }

        if ($headerValidation != $JWT->getHeader()) {

            throw new JwtMalformException();

        }

        if (!password_verify($preparedTokenSalted, $providedSignature)) {

            throw new JwtMalformException();

        }

        return $JWT;

    }

    /**
     * The generation method is described at https://jwt.io.
     * @return string
     */
    public function generateSignatureByHeaderAndPayload(): string
    {

        $preparedSignature = $this->prepareToken();
        return password_hash($preparedSignature . $_ENV["HASH_SALT"], PASSWORD_BCRYPT);

    }

    private function prepareToken(): string
    {

        return "{$this->getHeaderAsBase64()}.{$this->getPayloadAsBase64()}";

    }

    public function pack(): string
    {

        return "{$this->prepareToken()}.{$this->getSignatureAsBase64()}";

    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getHeaderAsBase64(): string
    {

        return base64_encode($this->getHeaderAsJson());

    }

    public function getHeaderAsJson(): bool|string
    {

        return json_encode($this->header);

    }

    public function getPayloadAsBase64(): string
    {

        return base64_encode($this->getPayloadAsJson());

    }

    public function getPayloadAsJson(): bool|string
    {

        return json_encode($this->payload);

    }

    public function getSignatureAsBase64(): string
    {

        return base64_encode($this->generateSignatureByHeaderAndPayload());

    }

    public function getPayload(): array
    {
        return $this->payload;
    }

}