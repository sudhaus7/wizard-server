<?php

namespace Sudhaus7\WizardServer;

use React\Promise\Deferred;

class Tables {


    public function fetch()
    {
        $deferred = new Deferred();
        $promise = $deferred->promise();

        $pdo = Database::getConnection();
        $res = $pdo->query( 'show tables');
        $result = $res->fetchAll(\PDO::FETCH_NUM);

        $tables = [];
        foreach($result as $table) {
            if (strpos($table[0],'zzz_')===0) {
                continue;
            }
            $tables[] = $table[0];
        }

        $deferred->resolve($tables);
        return $promise;
    }
}
