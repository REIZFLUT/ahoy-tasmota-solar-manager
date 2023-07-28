<?php

namespace App\Database;

use \PDO;
use \PDOException;
use SQLite3;

class Sqlite
{

    private static $host;
    private static $database;
    private static $user;
    private static $password;
    private static $port;

    private static $PDO;

    public static function init($file)
    {

        
        $dsn = 'sqlite:'.$file;

        self::$PDO = new PDO($dsn);

        self::$PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return self::$PDO;
    }

    private static function checkInit()
    {
        if (!is_a(self::$PDO, 'PDO')) {
            die('Database not initialized');
        }
    }


    public static function insert(string $table, array $assoc)
    {
        self::checkInit();

        $keys = array_keys($assoc);
        $vals = array_values($assoc);

        $fields = implode(', ', $keys);
        $nQm = substr(str_repeat(', ?', count($vals)), 2);

        $qu = self::$PDO->prepare('INSERT INTO ' . $table . '(' . $fields . ') VALUES (' . $nQm . ')');
        $qu->execute($vals);
        return self::$PDO->lastInsertId();
    }


    public static function query($query, $array = [], $fetchMode = 2, $objType = NULL)
    {
        self::checkInit();

        if (count($array) == 0) {
            try {
                $qu = self::$PDO->query($query);
                $re = ($objType == NULL) ? $qu->fetchAll($fetchMode) : $qu->fetchAll($fetchMode, $objType);
                return $re;
            } catch (PDOException $e) {
                
                return [];
            }
        } else {
            try {
                $qu = self::$PDO->prepare($query);
                $qu->execute($array);
                $re = ($objType == NULL) ? $qu->fetchAll($fetchMode) : $qu->fetchAll($fetchMode, $objType);
                return $re;
            } catch (PDOException $e) {
                return [];
            }
        }
    }

    public static function deleteById($table, $id, $idField = 'id')
    {
        self::checkInit();

        $query = 'DELETE FROM ' . $table . ' WHERE ' . $idField . ' = ?';

        try {
            $qu = self::$PDO->prepare($query);
            $qu->execute([$id]);
            return true;
        } catch (PDOException $e) {
            var_dump($e);
            return false;
        }
    }


    public static function selectById($table, $id, $fields = '*', $idField = 'id')
    {

        self::checkInit();

        if (is_array($fields)) {
            $fieldString = implode(', ', $fields);
        } else {
            if (is_string($fields)) {
                $fieldString = $fields;
            } else {
                $fieldString = '*';
            }
        }

        $query = 'SELECT ' . $fieldString . ' FROM ' . $table . ' WHERE ' . $idField . ' = ? LIMIT 0,1';

        try {
            $qu = self::$PDO->prepare($query);
            $qu->execute([$id]);
            $re = $qu->fetch(2);
            return $re;
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function updateById($table, $id, array $assoc)
    {
        $keys = array_keys($assoc);
        $vals = array_values($assoc);
        $n = count($keys);

        $sets = '';
        for ($i = 0; $i < $n; $i++) {
            $sets .= $keys[$i] . ' = ?, ';
        };
        $setters = substr($sets, 0, -2);

        $query = 'UPDATE ' . $table . ' SET ' . $setters . ' WHERE id = ?';
        $vals[] = $id;

        $qu = self::$PDO->prepare($query);
        return $qu->execute($vals);
    }


}
