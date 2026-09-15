<?php

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar as BasePostgresGrammar;

class PostgresGrammar extends BasePostgresGrammar
{
    /**
     * Wrap a table in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string|null  $table
     * @return string
     */
    public function wrapTable($table)
    {
        if (empty($table) || (is_string($table) && trim($table) === '')) {
            return '"sub"';
        }

        return parent::wrapTable($table);
    }

    /**
     * Compile a select query into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        $sql = parent::compileSelect($query);

        return preg_replace('/\s+as\s+""/i', ' as "sub"', $sql);
    }
}
