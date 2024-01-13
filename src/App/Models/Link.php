<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\Models\Exceptions\InvalidClientData;

class Link extends AbstractModel
{

    protected static string $tableName = "link";

    use LinkStatisticsTrait;

    public const URL_FORMAT_ERROR = "Url must match the format";
    const LINK_PER_PAGE = 5;

    public function __construct(private int $id, private Account $author, private string $url, private string $alias, private int $suspect)
    {
    }

    protected static function findByField(string $field, string|int|float|bool $value): false|self
    {

        $LinkRow = parent::findByField($field, $value);
        $Author = Account::getById($LinkRow["author"]);

        if (!$Author) return false;

        return $LinkRow ? new self($LinkRow["id"], $Author, $LinkRow["url"], $LinkRow["alias"], $LinkRow["suspect"]) : false;

    }

    public static function findById(int $id): false|self
    {

        return self::findByField("id", $id);

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

        $Pagination = new Pagination("link");
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