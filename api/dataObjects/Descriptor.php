<?php


class Descriptor implements CRUDL, ASerializable
{
    private int $id;
    private string $title, $description;
    private int $authorId;
    private int $delta, $lastEdit, $created;
    function __construct(PDO $database, ?LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }
    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM Descriptors WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = $id;
        $this->title = $r["title"];
        $this->authorId = (int) $r["authorId"];
        $this->description = $r["decription"];
        $this->delta = (float) $r["delta"];
        $this->lastEdit = (int) $r["lastEdit"];
        $this->created = (int) $r["created"];
    }
    public function list(): array
    {
        $q = $this->database->prepare("SELECT * FROM Descriptors");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new Descriptor($this->database, $this->loggedInUser);
            $u->deserialize($result);
            $final[] = $u;
        }
        return $final;
    }
    public function delete(): void
    {
        if (!$this->loggedInUser) throw new AuthErrorException();
        if ($this->loggedInUser->getUser()->getId() != $this->authorId) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM Descriptors WHERE id = :id");
        $p->execute([":id" => $this->id]);
        $p = $this->database->prepare("DELETE FROM DescriptorRecords WHERE recordId = :id");
        $p->execute([":id" => $this->id]);
        //cascade delete descriptor from all games
        /*
        «La P2 è stata sciolta da una legge, 
        ma può essere sopravvissuto il suo sistema di relazioni politiche, 
        finanziarie e criminali […] 
        Quanto al dottor Berlusconi, 
        il suo interventismo attuale è sintomo della reazione di una parte del vecchio regime che, 
        avendo accumulato ricchezza e potere negli anni Ottanta, 
        pretende di continuare a condizionare la vita politica anche negli anni Novanta»
        */


        $q = $this->database->prepare("SELECT id,descriptorIds FROM Games WHERE  descriptorIds LIKE ('%' || ',' || trim(lower(:did)) || ',' || '%') OR descriptorIds LIKE (trim(lower(:did)) || ',' || '%') OR descriptorIds LIKE ('%' || ',' || trim(lower(:did)) ) ");
        /**
         * how the descriptorIds is searched?
         * "a,..."
         * "...,a,..."
         * "...,a"
         */

        $q->execute([
            ":did" => (string) $this->id
        ]);
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($re as $result) {
            $ar = explode(",", $result["descriptorIds"]);
            if (($key = array_search($this->id, $ar)) !== false) {
                unset($ar[$key]);
            }
            $q = $this->database->prepare("UPDATE Games SET descriptorIds = :did WHERE id=:id");
            $q->execute([
                ":did" => implode(",", $ar),
                ":id" => $this->id
            ]);
        }
    }
    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        $q = $this->database->prepare("UPDATE Descriptors SET title=:title, description=:description, delta=:delta, lastEdit=:lastEdit WHERE id=:id");
        $q->execute([
            ":title" => $this->title,
            ":description" => $this->description,
            ":delta" => $this->delta,
            ":lastEdit" => time(),
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO Descriptors(authorId, title, description, delta lastEdit, created) VALUES(:authorId, :title, :description, :delta, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":authorId" => $this->authorId,
            ":title" => $this->title,
            ":delta" => $this->delta,
            ":created" => $this->created,
            ":id" => $this->id
        ]);
        $this->passwordHash = $hash;
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        return [];
    }
    public function serialize(): array
    {
    }
}
