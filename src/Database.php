<?php

namespace Sudhaus7\WizardServer;

class Database {
    public static function getConnection(): \PDO
    {
        $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s",getenv('WIZARD_SERVER_DBHOST'),getenv('WIZARD_SERVER_DBPORT'),getenv('WIZARD_SERVER_DBNAME'));
        $pdo = new \PDO($dsn, getenv('WIZARD_SERVER_DBUSER'), getenv('WIZARD_SERVER_DBPASS'));
        //$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        return $pdo;
    }

    public function getTableFields(string $table)
    {
        $pdo = self::getConnection();
        $sql = 'describe '.$table;
        $stmt = $pdo->prepare( $sql );
        $stmt->execute();
        $result = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $result[] = $row[0];
        }
        return $result;
    }

    public static function getRecord(string $table, int $uid, string $field='uid',bool $adddeleted = true)
    {
        $pdo = self::getConnection();
        $sql = sprintf('select * from %s where %s=:uid %s',$table,$field, $adddeleted ? 'and deleted=0':'');
        $stmt = $pdo->prepare( $sql );
        $stmt->execute(['uid'=>$uid]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        unset($pdo);
        foreach($row as $key=>$value) {
            if (MathUtility::canBeInterpretedAsFloat( $value)) {
                $row[$key]=(float)$value;
            }
            if (MathUtility::canBeInterpretedAsInteger( $value)) {
                $row[$key]=(int)$value;
            }
        }
        return $row;
    }
}
