<?php
/*
* File:     LengthAwarePaginator.php
* Category: Support
* Author:   M. Goldenbaum
* Created:  16.03.18 03:13
* Updated:  -
*
* Description:
*  LengthAwarePaginator class replacing Illuminate\Pagination\LengthAwarePaginator
*/

namespace Webklex\PHPIMAP\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Class LengthAwarePaginator
 *
 * @package Webklex\PHPIMAP\Support
 */
class LengthAwarePaginator implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    /**
     * All of the items being paginated.
     *
     * @var array
     */
    protected array $items;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    protected int $perPage;

    /**
     * The current page being viewed.
     *
     * @var int
     */
    protected int $currentPage;

    /**
     * The total number of items before slicing.
     *
     * @var int
     */
    protected int $total;

    /**
     * The last available page.
     *
     * @var int
     */
    protected int $lastPage;

    /**
     * The base path to assign to all URLs.
     *
     * @var string
     */
    protected string $path = '/';

    /**
     * The query parameters to add to all URLs.
     *
     * @var array
     */
    protected array $query = [];

    /**
     * The URL fragment to add to all URLs.
     *
     * @var string|null
     */
    protected ?string $fragment = null;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected string $pageName = 'page';

    /**
     * The paginator options.
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Create a new paginator instance.
     *
     * @param mixed $items
     * @param int $total
     * @param int $perPage
     * @param int|null $currentPage
     * @param array $options
     */
    public function __construct(mixed $items, int $total, int $perPage, ?int $currentPage = null, array $options = []) {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->total = $total;
        $this->perPage = max($perPage, 1);
        $this->lastPage = max((int) ceil($total / $this->perPage), 1);
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->items = is_array($items) ? $items : (($items instanceof Collection) ? $items->all() : iterator_to_array($items));
    }

    /**
     * Set the current page for the paginator.
     *
     * @param int|null $currentPage
     * @return int
     */
    protected function setCurrentPage(?int $currentPage): int {
        $currentPage = $currentPage ?: Paginator::resolveCurrentPage($this->pageName);

        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }

    /**
     * Determine if the given value is a valid page number.
     *
     * @param int $page
     * @return bool
     */
    protected function isValidPageNumber(int $page): bool {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get the URL for a given page number.
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = [$this->pageName => $page];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        $url = $this->path . '?' . http_build_query($parameters, '', '&');

        if ($this->fragment !== null) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl(): ?string {
        if ($this->currentPage > 1) {
            return $this->url($this->currentPage - 1);
        }

        return null;
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl(): ?string {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage + 1);
        }

        return null;
    }

    /**
     * Get the URL for the first page.
     *
     * @return string
     */
    public function firstPageUrl(): string {
        return $this->url(1);
    }

    /**
     * Get the URL for the last page.
     *
     * @return string
     */
    public function lastPageUrl(): string {
        return $this->url($this->lastPage);
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages(): bool {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages(): bool {
        return $this->currentPage != 1 || $this->hasMorePages();
    }

    /**
     * Determine if the paginator is on the first page.
     *
     * @return bool
     */
    public function onFirstPage(): bool {
        return $this->currentPage <= 1;
    }

    /**
     * Determine if the paginator is on the last page.
     *
     * @return bool
     */
    public function onLastPage(): bool {
        return !$this->hasMorePages();
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage(): int {
        return $this->currentPage;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function lastPage(): int {
        return $this->lastPage;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function total(): int {
        return $this->total;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage(): int {
        return $this->perPage;
    }

    /**
     * Get the number of the first item in the slice.
     *
     * @return int
     */
    public function firstItem(): int {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : 0;
    }

    /**
     * Get the number of the last item in the slice.
     *
     * @return int
     */
    public function lastItem(): int {
        return count($this->items) > 0 ? $this->firstItem() + count($this->items) - 1 : 0;
    }

    /**
     * Get all of the items being paginated.
     *
     * @return array
     */
    public function items(): array {
        return $this->items;
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count(): int {
        return count($this->items);
    }

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool {
        return empty($this->items);
    }

    /**
     * Determine if the list of items is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool {
        return !$this->isEmpty();
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if the given item exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->items[$offset]);
    }

    /**
     * Get the item at the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->items[$offset];
    }

    /**
     * Set the item at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->items[$offset] = $value;
    }

    /**
     * Unset the item at the given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void {
        unset($this->items[$offset]);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'current_page' => $this->currentPage,
            'data' => $this->items,
            'first_page_url' => $this->firstPageUrl(),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage,
            'last_page_url' => $this->lastPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage,
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total,
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Set the query string variable used to store the page.
     *
     * @param string $name
     * @return $this
     */
    public function setPageName(string $name): static {
        $this->pageName = $name;

        return $this;
    }

    /**
     * Get the query string variable used to store the page.
     *
     * @return string
     */
    public function getPageName(): string {
        return $this->pageName;
    }

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): static {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the base path for paginator generated URLs.
     *
     * @return string
     */
    public function path(): string {
        return $this->path;
    }

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     * @param string|null $value
     * @return $this
     */
    public function appends(array|string $key, ?string $value = null): static {
        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Add an array of query string values.
     *
     * @param array $keys
     * @return $this
     */
    protected function appendArray(array $keys): static {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Add a query string value to the paginator.
     *
     * @param string $key
     * @param string|null $value
     * @return $this
     */
    protected function addQuery(string $key, ?string $value): static {
        if ($key !== $this->pageName) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Set the URL fragment to be appended to URLs.
     *
     * @param string|null $fragment
     * @return $this
     */
    public function fragment(?string $fragment = null): static {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Get the paginator options.
     *
     * @return array
     */
    public function getOptions(): array {
        return $this->options;
    }
}
