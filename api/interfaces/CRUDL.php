<?php
interface CRUDL
{
    public function get(int $id): void;
    public function list(): array;
    public function delete(): void;
    public function update(): void;
    public function create(array $data): void;
    //public function __construct(PDO $database, ?LoggedInUser $loggedInUser); nullable is not universal
}
