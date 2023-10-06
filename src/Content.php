<?php

namespace Sudhaus7\WizardServer;

use Exception;
use PDO;
use React\Http\Message\Response;
use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;

class Content {

    protected $db;
    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db;
    }

    public function fetch(string $table, int $id, string $field = 'uid')
    {

        if ($this->db) {
            $sql = sprintf('select * from %s where %s=?',$table, $field);

            return $this->db->query( $sql, [ $id ] )
                            ->then( function ( QueryResult $queryResult ) {
                                $rows = $queryResult->resultRows ? $queryResult->resultRows : [];
                                foreach ( $rows as $idx => $row ) {
                                    foreach ( $row as $key => $value ) {
                                        if ( MathUtility::canBeInterpretedAsFloat( $value ) ) {
                                            $rows[ $idx ][ $key ] = (float) $value;
                                        }
                                        if ( MathUtility::canBeInterpretedAsInteger( $value ) ) {
                                            $rows[ $idx ][ $key ] = (int) $value;
                                        }
                                    }
                                }

                                return Response::json( $rows );
                            }, function () {
                                return Response::json( [] );
                            } );
        } else {
            $rows = [];
            try {
                $sql = sprintf( 'select * from %s where %s=:id', $table, $field );

                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare( $sql );
                $stmt->execute( [ 'id' => $id ] );
                $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
                foreach ( $rows as $idx => $row ) {
                    foreach ( $row as $key => $value ) {
                        if ( MathUtility::canBeInterpretedAsFloat( $value ) ) {
                            $rows[ $idx ][ $key ] = (float) $value;
                        }
                        if ( MathUtility::canBeInterpretedAsInteger( $value ) ) {
                            $rows[ $idx ][ $key ] = (int) $value;
                        }
                    }
                }
            } catch ( Exception $e) {

            }

            return Response::json( $rows );
        }
    }
}
