<?php
//id name surname

class Professor implements CRUDL, ASerializable//check
{
    private int $id;
    private string $name, $surname;
    private int $created, $lastEdit;
    private PDO $database;
    private LoggedInUser $loggedInUser;
    function __construct(PDO $database, LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }
    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM Professors WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = $id;
        $this->name = $r["name"];
        $this->surname = $r["surname"];
        $this->created = (int) $r["created"];
        $this->lastEdit = (int) $r["lastEdit"];

    }
    public function list(): array
    {
        $q = $this->database->prepare("SELECT * FROM Professors");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new Professor($this->database, $this->loggedInUser);
            $u->deserialize($result);
            $final[] = $u;
        }
        return $final;
    }
    public function delete(): void
    {
        if ($this->loggedInUser->getUser()->getId() != $this->id) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM Professors WHERE id = :id");
        $p->execute([":id" => $this->id]);
        //cascade delete descriptor from all games and snaicards

        // new OOP approach
        $game = new Game($this->database, $this->loggedInUser);
        $gameList = $game->list();
        foreach($gameList as $d){
            $l = $d->getDescriptorIds();
            $l->remove($this->id);
            $d->setDescriptorids($l);
            $p = $d->getProfessorIds();
            $p->remove($this->id);
            $d->setProfessorIds($l);
            $d->delete();
        }
       
    }
    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        $q = $this->database->prepare("UPDATE Professors SET name=:name, surname=:surname, lastEdit=:lastEdit WHERE id=:id");
        $q->execute([
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":lastEdit" => time(),
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO Professor(name, surname, lastEdit, created) VALUES( :name, :surname, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([//check
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":lastEdit" => $this->lastEdit,
            ":created" => $this->created,
            ":id" => $this->id
        ]);
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
    }
    public function serialize(): array
    {
        
    }
}
