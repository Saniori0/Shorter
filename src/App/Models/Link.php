<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\DatabaseConnection;
use Shorter\Backend\App\Models\Exceptions\InvalidClientData;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Utils\SypexGeo;

class Link
{

    public const URL_FORMAT_ERROR = "Url must match the format";
    const LINK_PER_PAGE = 5;

    public function __construct(private int $id, private Account $author, private string $url, private string $alias, private int $suspect)
    {
    }

    private static function getByField(string $field, string|int|float|bool $value): false|self
    {

        $Database = DatabaseConnection::getMysqlPdo();
        $Statement = $Database->prepare("SELECT * FROM link WHERE $field = ?");
        $Statement->execute([$value]);

        $LinkRow = @$Statement->fetchAll(\PDO::FETCH_ASSOC)[0];
        $Author = Account::getById($LinkRow["author"]);

        if (!$Author) return false;

        return $LinkRow ? new self($LinkRow["id"], $Author, $LinkRow["url"], $LinkRow["alias"], $LinkRow["suspect"]) : false;

    }

    public static function getById(int $id): false|self
    {

        return self::getByField("id", $id);

    }

    public static function getByAlias(string $alias): false|self
    {

        if (mb_strlen($alias) != 13) {

            return false;

        }

        return self::getByField("alias", $alias);

    }

    /**
     * Gets all account links with pagination
     * @param int $page
     * @return Link[]
     */
    public static function getByAuthorWithPagination(Account $author, int $page = 1): array
    {

        if ($page < 1) {

            return [];

        }

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("SELECT * FROM link WHERE author = :author ORDER BY id DESC LIMIT :linkPerPage OFFSET :pageOffset;");

        $AuthorId = $author->getId();
        $linkPerPage = Link::LINK_PER_PAGE;

        $pageOffset = ($page - 1) * $linkPerPage;

        $Statement->bindParam(":author", $AuthorId, \PDO::PARAM_INT);
        $Statement->bindParam(":linkPerPage", $linkPerPage, \PDO::PARAM_INT);
        $Statement->bindParam(":pageOffset", $pageOffset, \PDO::PARAM_INT);

        $Statement->execute();

        $links = $Statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_filter($links, fn(array $linkRow) => new self($linkRow["id"], $author, $linkRow["url"], $linkRow["alias"], $linkRow["suspect"]));

    }

    public static function countLinkPages(Account $author): float
    {

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("SELECT count(*) as links FROM link WHERE author = ?");
        $Statement->execute([$author->getId()]);

        $Result = $Statement->fetch(\PDO::FETCH_ASSOC);

        return ceil($Result["links"] / self::LINK_PER_PAGE);

    }

    /**
     * Suspicious URLs are those that do not have a domain, have three or more subdomains, or do not use https.
     * @param string $url
     * @return bool
     */
    public static function isUrlSuspect(string $url): bool
    {

        if (!str_contains($url, "https://")) return true;
        if (count(explode(".", $url)) > 2) return true;

        return false;

    }

    public static function create(Account $Author, string $url): Link
    {

        if (!filter_var($url, FILTER_VALIDATE_URL)) {

            throw new InvalidClientData(self::URL_FORMAT_ERROR);

        }

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("INSERT INTO link (author, url, alias, suspect) VALUES (?, ?, ?, ?)");

        $alias = uniqid();
        $suspect = self::isUrlSuspect($url);

        $Statement->execute([
            $Author->getId(),
            $url,
            $alias,
            (int)$suspect
        ]);

        $LinkId = $Database->lastInsertId();

        return new self(
            $LinkId,
            $Author,
            $url,
            $alias,
            $suspect
        );

    }

    /**
     * Secure interface to retrieve all account details
     * @return array
     */
    public function get()
    {

        return [
            "id" => $this->getId(),
            "author" => $this->getAuthor()->getId(),
            "url" => $this->getUrl(),
            "alias" => $this->getAlias(),
            "suspect" => $this->isSuspect()
        ];

    }

    public function replenishStats(): void
    {

        $geo = new SypexGeo();
        $ip = Request::getInstance()->getClientIp();
        $countryCode = $geo->getCountry($ip);
        $time = time();

        if($countryCode == "") $countryCode = "--";

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("INSERT INTO statistics (ip, country, time, link) VALUES (?, ?, ?, ?)");

        $Statement->execute([
            $ip,
            $countryCode,
            $time,
            $this->getId(),
        ]);

    }

    /**
     * @return array rows of stats
     */
    public function getStatistics(): array
    {

        $Database = DatabaseConnection::getMysqlPdo();

        $Statement = $Database->prepare("SELECT * FROM statistics WHERE link = ?");

        $Statement->execute([
            $this->getId(),
        ]);

        $Statistics = $Statement->fetchAll(\PDO::FETCH_ASSOC);

        return $Statistics;

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthor(): Account
    {
        return $this->author;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function isSuspect(): bool
    {
        return $this->suspect;
    }

}