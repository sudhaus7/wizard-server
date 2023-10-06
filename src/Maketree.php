<?php

namespace Sudhaus7\WizardServer;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use React\MySQL\ConnectionInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Maketree
{

    private int $pid;

    private array $result = [];
    public function __construct(int $pid)
    {
        $this->pid = $pid;
    }

    public function fetch()
    {

        $deferred = new Deferred();
        $promise = $deferred->promise();


        $this->runForPid($this->pid);
        $deferred->resolve($this->result);
        //$deferred->reject(\Throwable $reason);
        return $promise;
    }

    private function runForPid(int $pid)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare( 'SELECT uid FROM pages where pid=:pid');
        $stmt->execute(['pid'=>$pid]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->result[] = (int)$row['uid'];
            $this->runForPid( $row['uid']);
        }
        unset($pdo);
    }


}
