<?php

class Pagination {
    private $page;
    private $perPage;
    private $total;
    private $term;
    private $order;
    private $basePath;

    /**
     * Pagination value object.
     * @param int $page Current page number.
     * @param int $perPage Items per page.
     * @param int $total Total number of items.
     * @param string $term Search term.
     * @param string $order Sort order (ASC|DESC).
     * @param string $basePath Base path for links.
     * @return void
     */
    public function __construct($page = 1, $perPage = 6, $total = 0, $term = '', $order = 'DESC', $basePath = '') {
        $this->page = max(1, (int)$page);
        $this->perPage = max(1, (int)$perPage);
        $this->total = max(0, (int)$total);
        $this->term = $term;
        $this->order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $this->basePath = $basePath !== '' ? $basePath : '#';
    }

    public function getPage() {
        return $this->page;
    }

    public function getPerPage() {
        return $this->perPage;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getTerm() {
        return $this->term;
    }

    public function getOrder() {
        return $this->order;
    }

    public function getBasePath() {
        return $this->basePath;
    }

    public function getTotalPages() {
        if ($this->perPage <= 0) {
            return 0;
        }
        return (int)ceil($this->total / $this->perPage);
    }

    public function hasPrev() {
        return $this->page > 1;
    }

    public function hasNext() {
        return $this->page < $this->getTotalPages();
    }

    public function prevPage() {
        return $this->hasPrev() ? $this->page - 1 : 1;
    }

    public function nextPage() {
        $totalPages = $this->getTotalPages();
        return $this->hasNext() ? $this->page + 1 : ($totalPages > 0 ? $totalPages : 1);
    }

    /**
     * Build URL keeping current filters and per-page.
     * @param int $pageNumber Destination page.
     * @param array $extra Additional query params.
     * @return string
     */
    public function urlForPage($pageNumber, array $extra = []) {
        $pageNumber = max(1, (int)$pageNumber);
        $queryParams = [];
        if ($this->term !== '') {
            $queryParams['term'] = $this->term;
        }
        $queryParams['page'] = $pageNumber;
        $queryParams['perPage'] = $this->perPage;
        $queryParams['order'] = $this->order;
        if (!empty($extra)) {
            $queryParams = array_merge($queryParams, $extra);
        }
        return $this->basePath . '?' . http_build_query($queryParams);
    }
}
?>
