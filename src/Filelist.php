<?php

namespace Sudhaus7\WizardServer;

use React\Http\Message\Response;
use React\MySQL\ConnectionInterface;
use \PDO;

class Filelist {


    protected $db;
    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db;
    }

    public function fetch($id)
    {
        $rows = [];
        try {
            $sql = 'select * from sys_filemounts where uid=:id';

            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare( $sql );
            $stmt->execute( [ 'id' => $id ] );
            $filemount = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'select * from sys_file where identifier like :path and missing=0';
            $stmt = $pdo->prepare( $sql);
            $stmt->execute(['path'=>$filemount['path'].'%']);

            $clean = [];
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
                if (is_file(\getenv('WIZARD_SERVER_DOCROOT').'/fileadmin'.$rows[$idx]['identifier'])) {
                    $clean[]=$rows[$idx];
                }
            }
        } catch ( \Exception $e) {

        }
        return Response::json( $clean );
    }
}
