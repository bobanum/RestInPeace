<?php

namespace RestInPeace;

trait HasHateoas {
	public static $root = null;

	public function getLinks() {
		$links = [];
		$links['self'] = $this->getSelfLink();
		$links['collection'] = $this->getCollectionLink();
		return $links;
	}
	public function getUrl(...$parts) {
		if (self::$root === null) {
			$host = $_SERVER['HTTP_HOST'];
			$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
			self::$root = sprintf('%s://%s', $protocol, $host);
		}
		array_unshift($parts, self::$root, $this->name);
		$url = implode("/", $parts);
		return $url;
	}
	public function addHateoasArray(&$data) {
		foreach ($data as &$row) {
			$this->addHateoas($row);
		}
	}
	public function addHateoas(&$data) {
		$data['url'] = $this->getUrl($data['id']);	// TODO: use right PK
	}
	public function getSelfLink() {
		$links = [];
		$links['href'] = $this->getSelfHref();
		$links['rel'] = 'self';
		return $links;
	}
	public function getCollectionLink() {
		$links = [];
		$links['href'] = $this->getCollectionHref();
		$links['rel'] = 'collection';
		return $links;
	}
	public function getSelfHref() {
		$href = sprintf("%s/%s/%s", self::$root, $this->getTable(), $this->id);
		return $href;
	}
	public function getCollectionHref() {
		$href = sprintf("%s/%s", self::$root, $this->getTable());
		return $href;
	}
}
