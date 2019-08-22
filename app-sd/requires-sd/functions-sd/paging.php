<?php

class PagedResults {
    /* These are defaults */

    var $TotalResults;
    var $CurrentPage = 1;
    var $PageVarName = "page";
    var $PagePaggingType = "get";
    // var $ResultsPerPage = 20; 
    var $ResultsPerPage = 12;
    var $LinksPerPage = 15;

    function InfoArray() {
        $this->TotalPages = $this->getTotalPages();
        $this->CurrentPage = $this->getCurrentPage();
        $this->ResultArray = array(
            "PREV_PAGE" => $this->getPrevPage(),
            "NEXT_PAGE" => $this->getNextPage(),
            "CURRENT_PAGE" => $this->CurrentPage,
            "TOTAL_PAGES" => $this->TotalPages,
            "TOTAL_RESULTS" => $this->TotalResults,
            "PAGE_NUMBERS" => $this->getNumbers(),
            "MYSQL_LIMIT1" => $this->getStartOffset(),
            "MYSQL_LIMIT2" => $this->ResultsPerPage,
            "START_OFFSET" => $this->getStartOffset(),
            "END_OFFSET" => $this->getEndOffset(),
            "RESULTS_PER_PAGE" => $this->ResultsPerPage,
        );
        return $this->ResultArray;
    }

    /* Start information functions */

    function getTotalPages() {
        /* Make sure we don't devide by zero */
        if ($this->TotalResults != 0 && $this->ResultsPerPage != 0) {
            $result = ceil($this->TotalResults / $this->ResultsPerPage);
        }
        /* If 0, make it 1 page */
        if (isset($result) && $result == 0) {
            return 1;
        } else {
            return $result;
        }
    }

    function getStartOffset() {
        $offset = $this->ResultsPerPage * ($this->CurrentPage - 1);
        if ($offset != 0) {
            $offset++;
        }
        return $offset;
    }

    function getEndOffset() {
        if ($this->getStartOffset() > ($this->TotalResults - $this->ResultsPerPage)) {
            $offset = $this->TotalResults;
        } elseif ($this->getStartOffset() != 0) {
            $offset = $this->getStartOffset() + $this->ResultsPerPage - 1;
        } else {
            $offset = $this->ResultsPerPage;
        }
        return $offset;
    }

    function getCurrentPage() {
        if ($this->PagePaggingType == "get") {
            if (isset($_GET[$this->PageVarName])) {
                return $_GET[$this->PageVarName];
            } else {
                return $this->CurrentPage;
            }
        } else {
            if (isset($_POST[$this->PageVarName])) {
                return $_POST[$this->PageVarName];
            } else {
                return $this->CurrentPage;
            }
        }
    }

    function getPrevPage() {
        if ($this->CurrentPage > 1) {
            return $this->CurrentPage - 1;
        } else {
            return false;
        }
    }

    function getNextPage() {
        if ($this->CurrentPage < $this->TotalPages) {
            return $this->CurrentPage + 1;
        } else {
            return false;
        }
    }

    function getStartNumber() {
        $links_per_page_half = $this->LinksPerPage / 2;
        /* See if curpage is less than half links per page */
        if ($this->CurrentPage <= $links_per_page_half || $this->TotalPages <= $this->LinksPerPage) {
            return 1;
            /* See if curpage is greater than TotalPages minus Half links per page */
        } elseif ($this->CurrentPage >= ($this->TotalPages - $links_per_page_half)) {
            return $this->TotalPages - $this->LinksPerPage + 1;
        } else {
            return $this->CurrentPage - $links_per_page_half;
        }
    }

    function getEndNumber() {
        if ($this->TotalPages < $this->LinksPerPage) {
            return $this->TotalPages;
        } else {
            return $this->getStartNumber() + $this->LinksPerPage - 1;
        }
    }

    function getNumbers() {
        for ($i = $this->getStartNumber(); $i <= $this->getEndNumber(); $i++) {
            $numbers[] = $i;
        }
        return $numbers;
    }

}

?>