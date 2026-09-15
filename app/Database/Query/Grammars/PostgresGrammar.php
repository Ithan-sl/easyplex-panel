<?php

namespace App\Database\Query\Grammars;

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
        if (empty($table)) {
            return '"sub"';
        }

        return parent::wrapTable($table);
    }
}
