<?php

namespace im\search\components\query;

/**
 * Match query.
 *
 * @package im\search\components\query
 */
class Match extends SearchQuery
{
    /**
     * @var string
     */
    private $_field;

    /**
     * @var Term
     */
    private $_term;

    /**
     * Creates Math query.
     *
     * @param string $field
     * @param Term $term
     */
    function __construct($field, Term $term)
    {
        $this->_field = $field;
        $this->_term = $term;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->_field = $field;
    }

    /**
     * @return Term
     */
    public function getTerm()
    {
        return $this->_term;
    }

    /**
     * @param Term $term
     */
    public function setTerm($term)
    {
        $this->_term = $term;
    }
} 