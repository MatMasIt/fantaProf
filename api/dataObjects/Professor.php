<?php
//id name surname

class Professor implements CRUDL, ASerializable //check
{
    private int $id;
    private string $name, $surname, $photoUrl, $comment;
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
        $this->id = (int) $id;
        $this->name = (string) $r["name"];
        $this->surname = $r["surname"];
        $this->created = (int) $r["created"];
        $this->lastEdit = (int) $r["lastEdit"];
        $this->photoUrl = (string) $r["photoUrl"];
        $this-> comment = (string) $r["comment"];
    }
    public function list($preserialize = true): array
    {
        $q = $this->database->prepare("SELECT id FROM Professors");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new Professor($this->database, $this->loggedInUser);
            $u->get((int) $result["id"]);
            if (!$preserialize) $final[] = $u;
            else $final[] = $u->serialize();
        }
        return $final;
    }
    public function delete(): void
    {
        $p = $this->database->prepare("DELETE FROM Professors WHERE id = :id");
        $p->execute([":id" => $this->id]);
        //cascade delete descriptor from all games and snaicards

        // new OOP approach
        $game = new Game($this->database, $this->loggedInUser);
        $gameList = $game->list();
        foreach ($gameList as $d) {
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
        $q = $this->database->prepare("UPDATE Professors SET name=:name, surname=:surname, photoUrl=:photoUrl, comment = :comment, lastEdit=:lastEdit WHERE id=:id");
        $q->execute([
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":photoUrl" => $this->photoUrl,
            ":comment" => $this->comment,
            ":lastEdit" => time(),
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $q = $this->database->prepare("INSERT INTO Professors(name, surname, photoUrl, comment, lastEdit, created) VALUES( :name, :surname, :photoUrl, :comment, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([ //check
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":lastEdit" => $this->lastEdit,
            ":photoUrl" => $this->photoUrl,
            ":comment" => $this-> comment,
            ":created" => $this->created
        ]);
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        $this->name = (string) $r["name"];
        $this->surname = (string) $r["surname"];
        $this->photoUrl = (string) $r["photoUrl"];
        $this->comment = (string) $r["comment"];
    }
    public function serialize(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "surname" => $this->surname,
            "photoUrl" => $this->photoUrl,
            "comment" => $this->comment,
            "lastEdit" => date("c", $this->lastEdit),
            "created" => date("c", $this->created)

        ];
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of surname
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set the value of surname
     *
     * @return  self
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the value of created
     */
    public function getCreated()
    {
        return date("c", $this->created);
    }

    /**
     * Get the value of lastEdit
     */
    public function getLastEdit()
    {
        return date("c", $this->lastEdit);
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    public function getPhotoUrl(){
        return $this->photoUrl;
    }

    public function setPhotoUrl(string $url){
        $this->photoUrl = $url;
    }

    public function getComment(){
        return $this->comment;
    }

    public function setComment(string $comment){
        $this->comment = $comment;
    }
}
