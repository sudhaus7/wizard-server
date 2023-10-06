<?php

namespace Sudhaus7\WizardServer;

use React\Http\Message\Response;
use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;

class Content {

    protected $db;
    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function fetch(string $table, int $id, string $field = 'uid')
    {
        $sql = sprintf('select * from %s where %s=?',$table, $field);
        return $this->db->query($sql,[$id])
                  ->then(function ( QueryResult $queryResult) {
                      $rows = $queryResult->resultRows ?? [];
                      foreach($rows as $idx=>$row) {
                          foreach($row as $key=>$value) {
                              if (MathUtility::canBeInterpretedAsFloat( $value)) {
                                  $rows[$idx][$key]=(float)$value;
                              }
                              if (MathUtility::canBeInterpretedAsInteger( $value)) {
                                  $rows[$idx][$key]=(int)$value;
                              }
                          }
                      }
                      return Response::json( $rows  );
                  });
    }
}
