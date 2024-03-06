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
        $fields = Database::getTableFields($table);
        if ($this->db) {
            $sql = sprintf('select * from %s where %s=?',$table, $field);
            if (\in_array( 'deleted', $fields)) {
                $sql .= ' AND deleted=0';
            }
            return $this->db->query( $sql, [ $id ] )
                            ->then( function ( QueryResult $queryResult ) {
                                $rows = $queryResult->resultRows ? $queryResult->resultRows : [];
                                foreach ( $rows as $idx => $row ) {
                                    foreach ( $row as $key => $value ) {
                                        if ($table==='tt_address' && strpos($key,'tx_cal_controller_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
                                        if ($table==='tt_address' && strpos($key,'tx_odsajaxmailsubscription_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
                                        if ($table==='tt_address' && strpos($key,'module_sys_dmail_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
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
                if (\in_array( 'deleted', $fields)) {
                    $sql .= ' AND deleted=0';
                }
                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare( $sql );
                $stmt->execute( [ 'id' => $id ] );
                $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
                foreach ( $rows as $idx => $row ) {
                    foreach ( $row as $key => $value ) {
                        if ($table==='tt_address' && strpos($key,'tx_cal_controller_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
                        if ($table==='tt_address' && strpos($key,'tx_odsajaxmailsubscription_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
                        if ($table==='tt_address' && strpos($key,'module_sys_dmail_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
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

    public function filter(string $table, string $field, array $config)
    {
        $result = [];
        try {

            $w = [$field.' in ('.$config['values'].')'];
            $fields = Database::getTableFields($table);
            if (\in_array( 'deleted', $fields)) {
                $w[] = ' deleted=0 ';
            }
            $sql = sprintf('select distinct %s from %s where %s',$field, $table,implode(" AND ",$w));
            //var_dump($sql);exit;
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare( $sql );
            $stmt->execute();
            $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $result = [];
            foreach ( $rows as $idx => $row ) {
                foreach ( $row as $key => $value ) {
                    if ( MathUtility::canBeInterpretedAsInteger( $value )
                     ) {
                        $result[] = (int) $value;
                    } else if ( MathUtility::canBeInterpretedAsFloat( $value ) ) {
                        $result[] = (float) $value;
                    } else {
                        $result[] = $value;
                    }
                }
            }
        } catch ( Exception $e) {

        }

        return Response::json( $result );
    }

    public function fetchComplex(string $table, array $config)
    {
        if ($this->db) {
            $w = [];
            foreach($config as $k=>$v) {
                $w[]= ' `'.$k.'`=? ';
            }
            $fields = Database::getTableFields($table);
            if (\in_array( 'deleted', $fields)) {
                $w[] = ' deleted=0 ';
            }
            $sql = sprintf('select * from %s where %s',$table,implode(" AND ",$w));
            return $this->db->query( $sql, $config )
                            ->then( function ( QueryResult $queryResult ) {
                                $rows = $queryResult->resultRows ? $queryResult->resultRows : [];
                                foreach ( $rows as $idx => $row ) {
                                    foreach ( $row as $key => $value ) {
                                        if ($table==='tt_address' && strpos($key,'tx_cal_controller_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
                                        if ($table==='tt_address' && strpos($key,'tx_odsajaxmailsubscription_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
                                        if ($table==='tt_address' && strpos($key,'module_sys_dmail_')!==false) {
                                            unset($rows[ $idx ][ $key ]);
                                            continue;
                                        }
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
                $w = [];
                foreach($config as $k=>$v) {
                    $w[]= ' `'.$k.'`=:'.$k.' ';
                }
                $fields = Database::getTableFields($table);
                if (\in_array( 'deleted', $fields)) {
                    $w[] = ' deleted=0 ';
                }
                $sql = sprintf('select * from %s where %s',$table,implode(" AND ",$w));
                //var_dump($sql);exit;
                $pdo  = Database::getConnection();
                $stmt = $pdo->prepare( $sql );
                $stmt->execute( $config );
                $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
                foreach ( $rows as $idx => $row ) {
                    foreach ( $row as $key => $value ) {
                        if ($table==='tt_address' && strpos($key,'tx_cal_controller_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
                        if ($table==='tt_address' && strpos($key,'tx_odsajaxmailsubscription_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
                        if ($table==='tt_address' && strpos($key,'module_sys_dmail_')!==false) {
                            unset($rows[ $idx ][ $key ]);
                            continue;
                        }
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
