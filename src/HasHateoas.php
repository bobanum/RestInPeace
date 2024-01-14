<?php

namespace RestInPeace;

/**
 * Trait HasHateoas
 * @package RestInPeace
 */
trait HasHateoas {
    /**
     * @var string $root The root URL for HATEOAS links.
     */
    public static $root = null;

    /**
     * Get the HATEOAS links.
     *
     * @return array The array of HATEOAS links.
     */
    public function getLinks() {
        $links = [
            'self' => $this->getSelfLink(),
            'collection' => $this->getCollectionLink(),
        ];
        return $links;
    }

    /**
     * Get the full URL for the specified parts.
     *
     * @param mixed ...$parts The parts to append to the URL.
     * @return string The constructed URL.
     */
    public function getUrl(...$parts) {
        $root = $this->getRoot();
        array_unshift($parts, $root, $this->name);
        $url = implode("/", $parts);
        return $url;
    }

    /**
     * Get the request scheme (http or https).
     *
     * @return string The request scheme.
     */
    private function getRequestScheme() {
        return $_SERVER['REQUEST_SCHEME'] ?? 'http';
    }

    /**
     * Get the root URL for HATEOAS links.
     *
     * @return string The root URL.
     */
    private function getRoot() {
        if (self::$root === null) {
            self::$root = sprintf('%s://%s', $this->getRequestScheme(), $this->getHttpHost());
        }
        return self::$root;
    }

    /**
     * Add HATEOAS links to an array of data.
     *
     * @param array $data The array of data to add links to.
     */
    public function addHateoasArray(&$data) {
        foreach ($data as &$row) {
            $this->addHateoas($row);
        }
    }

    /**
     * Add HATEOAS links to a piece of data.
     *
     * @param mixed $data The data to add links to.
     */
    public function addHateoas(&$data) {
        $data['url'] = $this->getUrl($data['id']); // TODO: use the right PK
    }

    /**
     * Get the self link for HATEOAS.
     *
     * @return array The self link.
     */
    public function getSelfLink() {
        $links = [];
        $links['href'] = $this->getSelfHref();
        $links['rel'] = 'self';
        return $links;
    }

    /**
     * Get the collection link for HATEOAS.
     *
     * @return array The collection link.
     */
    public function getCollectionLink() {
        $links = [
            'href' => $this->getCollectionHref(),
            'rel' => 'collection',
        ];
        return $links;
    }

    /**
     * Get the self href for HATEOAS.
     *
     * @return string The self href.
     */
    public function getSelfHref() {
        $href = sprintf("%s/%s/%s", self::$root, $this->getTable(), $this->id);
        return $href;
    }

    /**
     * Get the collection href for HATEOAS.
     *
     * @return string The collection href.
     */
    public function getCollectionHref() {
        $href = sprintf("%s/%s", self::$root, $this->getTable());
        return $href;
    }

    /**
     * Get the HTTP host from the server.
     *
     * @return mixed The HTTP host.
     */
    private function getHttpHost() {
        return $_SERVER['HTTP_HOST'];
    }
}
