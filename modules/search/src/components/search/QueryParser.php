<?php

namespace im\search\components\search;

use im\search\components\parser\QueryLexer;
use Symfony\Component\ExpressionLanguage\Lexer;
use Symfony\Component\ExpressionLanguage\Parser;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive;
use ZendSearch\Lucene\Search\Query\Boolean;

class QueryParser
{
    const OPERATOR_OR  = 0;
    const OPERATOR_AND = 1;

    public $parametersSeparator = '&';

    public $defaultOperator = self::OPERATOR_AND;

    /**
     * @param string $queryStr
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function parse($queryStr)
    {
        $query = '';
//        Analyzer::setDefault(new CaseInsensitive());
//        $query = new Boolean();
//        $parts = explode($this->parametersSeparator, $queryStr);
//        foreach ($parts as $subQuery) {
//            $subQuery = \ZendSearch\Lucene\Search\QueryParser::parse($subQuery);
//            $query->addSubquery($subQuery, $this->defaultOperator);
//        }
//        $lexer = new Lexer();
//        $query = $lexer->tokenize($queryStr);
//
//        $parser = new Parser([]);
//        $query = $parser->parse($query);

        $lexer = new QueryLexer();
        $tokens = $lexer->tokenize('title=one-or-to');

        return $query;
    }
}