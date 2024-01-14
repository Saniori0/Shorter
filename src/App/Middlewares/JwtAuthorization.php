<?php


namespace Shorter\Backend\App\Middlewares;

use Exception;
use Shorter\Backend\App\Middlewares\Exceptions\AuthException;
use Shorter\Backend\App\Models\Account;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Http\Response;
use Shorter\Backend\Routing\AbstractMiddleware;
use Shorter\Backend\Utils\Exceptions\JwtException;
use Shorter\Backend\Utils\JWT;

class JwtAuthorization extends AbstractMiddleware
{

    public static $dataName = "JwtAuth";

    public static function execute()
    {

        try {

            $token = @Request::getInstance()->getHeaderLine("x-jwt") ?? "";

            $JWT = JWT::verify($token, [
                "alg" => "bcrypt",
                "typ" => "AccountJWT",
            ]);

            $AccountID = @$JWT->getPayload()["id"];
            $Account = Account::findById($AccountID);

            if (!$Account) throw new AuthException("Account with provided id not exist");

            return $Account;

        } catch (Exception $e) {

            Response::json(403, [
                "message" => "Jwt not verified",
                "data" => []
            ])->dispatch();

        }

    }

}