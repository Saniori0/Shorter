<?php


namespace Shorter\Backend\Routing;

/**
 * This class allows you to manipulate, compare and obtain information about the path.
 * */
class Path
{

    public function __construct(private string $pathString)
    {
    }

    public function __toString()
    {

        return $this->pathString;

    }

    /**
     * Checks if a path matches other path
     *
     * Example 1: /test1/test2/ -> (^\/|)test1\/test2(\/|)$
     * Suitable routes:
     * test1/test2
     * /test1/test2/
     * /test1/test2
     * test1/test2/
     *
     * Example 2: /sayHello/:name -> (^\/|)sayHello\/:[a-zA-Z0-9а-яёА-ЯЁ\@\->]+(\/|)$
     * Suitable routes:
     * sayHello/:YourName
     * /sayHello/:YourName
     * sayHello/:YourName/
     *
     * @param Path $otherPath
     * @return bool
     */
    public function isMatchWith(string $otherPath): bool
    {

        return preg_match($this->generateRegexFromPathString(), $otherPath);

    }

    /**
     * Regex generated from pathString
     * Using this regex you can check a string to match a path (Path::isMatchWith)
     *
     * @return string
     */
    public function generateRegexFromPathString(): string
    {

        $pathEscapedSlash = str_replace("/", "\/", $this->pathString);

        if (mb_strlen(str_replace("/", "", $this->pathString)) > 0) {

            $pathEscapedSlash = trim($pathEscapedSlash, "\/");

            $pathRegex = "(^\/|)" . $pathEscapedSlash;
            $pathRegex .= "(\/|)$";

            foreach ($this->getFullParams() as $paramIndex => $paramName) {

                $pathRegex = str_replace(":$paramName", ":[a-zA-Z0-9а-яёА-ЯЁ\@\->]+", $pathRegex);

            }

        } else {

            $pathRegex = $pathEscapedSlash;

        }

        return "/$pathRegex/u";

    }

    /**
     * Return array of params, with hooks
     * Example: /sayReverseHello/:firstname@reverse->all/:lastname -> [
     *  "firstname@reverse->all",
     *  "lastname",
     * ];
     *
     * @return array
     */
    public function getFullParams(): array
    {

        preg_match_all('/([^a-zA-Z0-9а-яёА-ЯЁ]|^)(:)([a-zA-Z0-9а-яёА-ЯЁ]+)(@[a-zA-Z0-9а-яёА-ЯЁ]+->[a-zA-Z0-9а-яёА-ЯЁ]+|)/u', $this->pathString, $matches);

        $params = [];

        foreach ($matches[3] as $index => $name) {

            $params[$index] = $name . $matches[4][$index];

        }

        return $params;

    }

    /**
     * Return array of hooks, with param as a key
     * Example: /sayReverseHello/:firstname@reverse->all/:lastname -> [
     *  "firstname" => [
     *      "hookName" => "reverse",
     *      "hookValue" => "all"
     *  ],
     *  "lastname" => [],
     * ];
     *
     * @return array
     */
    public function getHooks()
    {

        $params = $this->getFullParams();
        $hooks = [];

        foreach ($params as $param) {

            preg_match_all("/^([a-zA-Z0-9а-яёА-ЯЁ]+)@([a-zA-Z0-9а-яёА-ЯЁ]+)->([a-zA-Z0-9а-яёА-ЯЁ]+)$/", $param, $matches);

            if (count($matches[2]) <= 0) continue;

            $hooks[str_replace("@" . $matches[2][0] . "->" . $matches[3][0], "", $param)] = [
                "hookName" => $matches[2][0],
                "hookBody" => $matches[3][0]
            ];

        }

        return $hooks;

    }

    /**
     * Return array of params, without hooks
     * Example: /sayReverseHello/:firstname@reverse->all/:lastname -> [
     *  "firstname",
     *  "lastname",
     * ];
     *
     * @return array
     */
    public function getParams(): array
    {

        preg_match_all('/([^a-zA-Z0-9а-яёА-ЯЁ]|^)(:)([a-zA-Z0-9а-яёА-ЯЁ]+)/u', $this->pathString, $matches);

        return $matches[3];

    }

}