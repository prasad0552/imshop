<?php

namespace im\search\components\query\parser;

use im\search\components\query\SearchQueryInterface;

/**
 * Query parser interface.
 *
 * @package im\search\components\query\parser
 */
interface QueryParserInterface
{
    /**
     * Parses query string.
     *
     * @param string $queryString
     * @param QueryParserContextInterface $context
     * @return SearchQueryInterface
     */
    public function parse($queryString, QueryParserContextInterface $context = null);
}