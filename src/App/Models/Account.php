<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\DatabaseConnection;
use Shorter\Backend\App\Models\Exceptions\InvalidClientData;
use Shorter\Backend\Utils\JWT;

class Account
{

    public const USERNAME_FORMAT_ERROR = "Username length must be >= 6 and <= 24";
    public const PASSWORD_FORMAT_ERROR = "Password length must be >= 6 and <= 24";
    public const EMAIL_FORMAT_ERROR = "Email must match the format";
    public const EMAIL_BUSY_ERROR = "Email busy";
    public const UNAUTHORIZED_ERROR = "Unauthorized";

    private function __construct(private int $id, private string $username, private string $password, private string $email)
    {
    }

    private static function getByField(string $field, string|int|float|bool $value): false|self
    {

        $Database = DatabaseConnection::getMysqlPdo();
        $Statement = $Database->prepare("SELECT * FROM account WHERE $field = ?");
        $Statement->execute([$value]);

        $AccountRow = @$Statement->fetchAll(\PDO::FETCH_ASSOC)[0];

        return $AccountRow ? new self($AccountRow["id"], $AccountRow["username"], $AccountRow["password"], $AccountRow["email"]) : false;

    }

    public static function getById(int $id): false|self
    {

        return self::getByField("id", $id);

    }

    public static function getByEmail(string $email): false|self
    {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            return false;

        }

        return self::getByField("email", $email);

    }

    public function generateJWT(): JWT
    {

        return new JWT([
            "alg" => "bcrypt",
            "typ" => "AccountJWT",
        ], [
            "id" => $this->getId(),
        ], true);

    }

    /**
     * Secure interface to retrieve all account details
     * @return array
     */
    public function get(): array
    {

        return [
            "id" => $this->getId(),
            "username" => $this->getUsername(),
            "email" => $this->getEmail(),
        ];

    }

    public function createLink(string $url): Link
    {

        return Link::create($this, $url);

    }

    public function hasRightOnLink(int $linkId): bool
    {

        return false;

    }

    public function countLinkPages(): float
    {

        return Link::countLinkPages($this);

    }

    public function getLinksWithPagination(int $page = 1): array
    {

        return Link::getByAuthorWithPagination($this, $page);

    }

    /**
     * Signup account
     * @param string $username
     * @param string $password
     * @param string $email
     * @return self
     */
    public static function create(string $username, string $password, string $email): self
    {

        if (mb_strlen($username) < 6 || mb_strlen($username) > 24) {

            throw new InvalidClientData(self::USERNAME_FORMAT_ERROR);

        }

        if (mb_strlen($password) < 6 || mb_strlen($password) > 24) {

            throw new InvalidClientData(self::PASSWORD_FORMAT_ERROR);

        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new InvalidClientData(self::EMAIL_FORMAT_ERROR);

        }

        if (self::getByEmail($email)) {

            throw new InvalidClientData(self::EMAIL_BUSY_ERROR);

        }

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("INSERT INTO account (username, password, email) VALUES (?, ?, ?)");

        $Statement->execute([
            $username,
            password_hash($password . $_ENV["HASH_SALT"], PASSWORD_BCRYPT),
            $email
        ]);

        $AccountId = $Database->lastInsertId();

        return new self(
            $AccountId,
            $username,
            $password,
            $email
        );

    }

    /**
     * Login account
     * @param string $email
     * @param string $password
     * @return false|self
     */
    public static function getAccountByLogin(string $email, string $password): false|self
    {

        if (mb_strlen($password) < 6 || mb_strlen($password) > 24) {

            throw new InvalidClientData(self::UNAUTHORIZED_ERROR);

        }

        $Account = self::getByEmail($email);

        if (!$Account) throw new InvalidClientData(self::UNAUTHORIZED_ERROR);

        if (!password_verify($password . $_ENV["HASH_SALT"], $Account->password)) throw new InvalidClientData(self::UNAUTHORIZED_ERROR);

        return $Account;

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

}