<?php

class IdFieldList
{
    private array $idList;
    private CRUDL $objType;
    public function __construct(CRUDL $objType)
    {
        $this->objType = $objType;
        $this->idList = [];
    }
    public function loadString(string $string): void
    { // ignore errors, database may contain old data
        $arr = explode(",", $string);
        foreach ($arr as $el) {
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
        $this->loadString(implode(",", $list));
    }
    public function remove(int $id): bool
    {
        if (($key = array_search($id, $this->idList)) !== false) {
            unset($messages[$key]);
            return true;
        }
        return false;
    }
    public function add(int $id): void
    {
        $this->idList[] = $id;
    }
    public function size(): int
    {
        return count($this->idList);
    }
}
