<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\Models\Exceptions\InvalidClientData;
use Shorter\Backend\Utils\Pagination;

class Link extends AbstractModel
{

    use LinkStatisticsTrait;

    public const URL_FORMAT_ERROR = "Url must match the format";

    const LINK_PER_PAGE = 5;

    protected static string $tableName = "link";

    public function __construct(protected int $id, private Account $author, private string $url, private string $alias, private int $suspect)
    {
    }

    protected static function findByField(string $field, string|int|float|bool $value): false|self
    {

        $LinkRow = parent::findByField($field, $value);
        $Author = Account::findById($LinkRow["author"]);

        if (!$Author) return false;

        return $LinkRow ? new self($LinkRow["id"], $Author, $LinkRow["url"], $LinkRow["alias"], $LinkRow["suspect"]) : false;

    }

    public static function findByAlias(string $alias): false|self
    {

        if (mb_strlen($alias) != 13) {

            return false;

        }

        return self::findByField("alias", $alias);

    }

    public static function generateLinksPaginationByAuthor(Account $author): Pagination
    {

        $Pagination = new Pagination(self::$tableName);
        $Pagination->where("author = ?", [$author->getId()]);

        return $Pagination;

    }

    /**
     * Gets all account links with pagination
     * @param int $page
     * @return Link[]
     */
    public static function findByAuthorWithPagination(Account $author, int $page = 1): array
    {

        $linkRows = self::generateLinksPaginationByAuthor($author)->getRowsByPageNumber($page);
        return array_filter($linkRows, fn(array $linkRow) => new self($linkRow["id"], $author, $linkRow["url"], $linkRow["alias"], $linkRow["suspect"]));

    }

    public static function countLinkPages(Account $author): float
    {

        return self::generateLinksPaginationByAuthor($author)->countPages();

    }

    public static function create(Account $Author, string $url): Link
    {

        if (!filter_var($url, FILTER_VALIDATE_URL)) {

            throw new InvalidClientData(self::URL_FORMAT_ERROR);

        }

        $Statement = self::getMysqlPdo()->prepare("INSERT INTO link (author, url, alias, suspect) VALUES (?, ?, ?, ?)");

        $alias = uniqid();
        $suspect = self::isUrlSuspect($url);

        $Statement->execute([
            $Author->getId(),
            $url,
            $alias,
            (int)$suspect
        ]);

        $LinkId = self::getMysqlPdo()->lastInsertId();

        return new self(
            $LinkId,
            $Author,
            $url,
            $alias,
            $suspect
        );

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

    /**
     * Secure interface to retrieve all link details
     * @return array
     */
    public function get()
    {

        return [
            "id" => $this->getId(),
            "author" => $this->getAuthor()->getId(),
            "url" => $this->getUrl(),
            "alias" => $this->getAlias(),
            "suspect" => $this->isSuspect(),
        ];

    }

    public function isSuspect(): bool
    {
        return $this->suspect;
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

    public function isAuthor(Account $author): bool
    {

        return $author->getId() == $this->getAuthor()->getId();

    }

}