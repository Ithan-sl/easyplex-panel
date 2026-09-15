<?php

namespace App\Database;

use App\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \App\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new PostgresGrammar);
    }
}
