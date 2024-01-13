<?php


namespace Shorter\Backend\App\Middlewares;

use Shorter\Backend\App\Middlewares\Exceptions\JwtAuthException;
use Shorter\Backend\App\Models\Account;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Http\Response;
use Shorter\Backend\Routing\AbstractMiddleware;
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
            $Account = Account::getById($AccountID);

            if(!$Account) throw new JwtAuthException("Account with provided id not exist");

            return $Account;

        } catch (\Shorter\Backend\Utils\Exceptions\JwtException $e) {

            Response::json(403, [
                "message" => "Jwt not verified",
                "data" => []
            ])->dispatch();

        }

    }

}