<?php

class IdFieldList
{
    private array $idList;
    private CRUDL $objType;
    private PDO $database;
    public function __construct(PDO $database, CRUDL $objType)
    {
        $this->database = $database;
        $this->idList = [];
    }
    public function loadString(string $string): void
    { // ignore errors, database may contain old data
        $arr = explode(",", $string);
        $this->idList = [];
        foreach ($arr as $el) {
            if (!is_int($el)) continue;
            $el = (int) $el;
            try {
                $this->objType->get($el);
            } catch (Exception $e) {
                continue;
            }
            $this->idList[] = $el;
        }
    }
    public function __toString(): string
    {
        return implode(",", $this->idList);
    }
    public function getList(): array
    {
        return $this->idList;
    }
    public function setList(array $list): void
    {
        $this->idList = $this->loadString(implode(",", $list));
    }
}
