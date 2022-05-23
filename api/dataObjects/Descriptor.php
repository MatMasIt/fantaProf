<?php


class Descriptor implements CRUDL, ASerializable
{
    private int $id;
    private string $title, $description;
    private int $authorId;
    private int $delta, $lastEdit, $created;
    private PDO $database;
    private LoggedInUser $loggedInUser;
    function __construct(PDO $database, LoggedInUser $loggedInUser)
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
    public function list($preserialize = true): array
    {
        $q = $this->database->prepare("SELECT id FROM Descriptors");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new Descriptor($this->database, $this->loggedInUser);
            $u->get((int) $result["id"]);
            if (!$preserialize) $final[] = $u;
            else $final[] = $u->serialize();
        }
        return $final;
    }
    public function delete(): void
    {
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
        // new OOP approach
        $game = new Game($this->database, $this->loggedInUser);
        $gameList = $game->list();
        foreach ($gameList as $d) {
            $l = $d->getDescriptorIds();
            $l->remove($this->id);
            $d->setDescriptorids($l);
            $d->update();
        }
    }
    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        $q = $this->database->prepare("UPDATE Descriptors SET title=:title, authorId=:authorId, description=:description, delta=:delta, lastEdit=:lastEdit WHERE id=:id");
        $q->execute([
            ":title" => $this->title,
            ":authorId" => $this->authorId,
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
        $q = $this->database->prepare("INSERT INTO Descriptors(authorId, title, description, delta, lastEdit, created) VALUES(:authorId, :title, :description, :delta, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":authorId" => $this->loggedInUser->getUser()->getId(),
            ":title" => $this->title,
            ":delta" => $this->delta,
            ":created" => $this->created,
            ":description" => $this->description,
            ":lastEdit" => $this->lastEdit,
            ":id" => $this->id
        ]);
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        $this->authorId = (int) $r["authorId"];
        $this->title = (string) $r["title"];
        $this->delta = (float) $r["delta"];
        $this->delta = (string) $r["description"];
    }
    public function serialize(): array
    {
        return [
            "id" => $this->id,
            "authorId" => $this->authorId,
            "title" => $this->title,
            "description" => $this->description,
            "delta" => $this->delta
        ];
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of authorId
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * Get the value of delta
     */
    public function getDelta()
    {
        return $this->delta;
    }

    /**
     * Set the value of delta
     *
     * @return  self
     */
    public function setDelta($delta)
    {
        $this->delta = $delta;

        return $this;
    }

    /**
     * Get the value of lastEdit
     */
    public function getLastEdit()
    {
        return date("c", $this->lastEdit);
    }

    /**
     * Get the value of created
     */
    public function getCreated()
    {
        return date("c", $this->created);
    }
}
