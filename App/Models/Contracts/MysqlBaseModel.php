<?php

namespace App\Models\Contracts;
use Medoo\Medoo;


use App\Core\Request;
use PDO;

class MysqlBaseModel extends BaseModel{
    public function __construct($id = null)
    {
        try{
//            $this->connection = new PDO("mysql:dbname={$_ENV['DB_NAME']};host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
//            $this->connection->exec("set names utf8;");
            $this->connection = new Medoo([
                'type' => 'mysql',
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_NAME'],
                'username' =>  $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASS'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'port' => $_ENV['DB_PORT'],
                'logging' => true,
                'error' => PDO::ERRMODE_EXCEPTION,
                'command' => [
                    'SET SQL_MODE=ANSI_QUOTES'
                ]
            ]);
        }   catch (\PDOException $e){
            echo "Connection Failed: " . $e->getMessage();
        }
        if(!is_null($id)){
            return $this->find($id);
        }
    }
    public function remove() : ?int{
        $record_id = $this->{$this->primaryKey};
        return $this->delete([$this->primaryKey => $record_id]);
//        return $record_id != null ? $record_id : null;
    }
    public function save(){
        $record_id = $this->{$this->primaryKey};
        $this->update($this->attributes, [$this->primaryKey => $record_id]);
        return $this->find($record_id);
    }
    public function create(array $data) : int
    {
        $this->connection->insert($this->table, $data);
        return $this->connection->id();
    }
    public function find($id) : object
    {
        $record = $this->connection->get($this->table, '*', [$this->primaryKey => $id]);
        if(is_null($record)) return (object)null;
        foreach ($record as $col => $val){
            $this->attributes[$col] = $val;
        }
        return $this;
    }
    public function getAll(): array{
        return $this->connection->select($this->table, '*');
    }
    public function get(array $columns, array $where) : array
    {
        return $this->connection->select($this->table, $columns, $where);
    }
    public function update(array $data, array $where) : int
    {
        $result = $this->connection->update($this->table, $data, $where);
        return $result->rowCount();
    }
    # Delete
    public function delete(array $where) : int
    {
        $result = $this->connection->update($this->table, $where);
        return $result->rowCount();
    }
    public function count(array $where): int{
        return $this->connection->count($this->table, $where);
    }
    public function sum($column, array $where): int{
        return $this->connection->sum($this->table, $column, $where);
    }
}