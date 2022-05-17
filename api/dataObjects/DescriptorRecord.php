<?php


class DescriptorRecord implements CRUDL, ASerializable
{
    private int $id;
    private int $SNAICardId, $profId, $descriptorId;
    private int $instant;
    private string $comment;
    private PDO $database;
    private LoggedInUser $loggedInUser;
    function __construct(PDO $database, LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }
    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM DescriptorRecords WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = (int) $id;
        $this->SNAICardId = (int) $r["SNAICardId"];
        $this->profId = (int) $r["profId"];
        $this->descriptorId = (int) $r["descriptorId"];
        $this->instant = (int) $r["instant"];
        $this->comment = (string) $r["comment"];
    }
    public function list(): array
    {
        $q = $this->database->prepare("SELECT * FROM DescriptorRecords");
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
        $snai = new SNAICard($this->database, $this->loggedInUser);
        $snai->get($this->SNAICardId);
        if ($this->loggedInUser->getUser()->getId() != $snai->getUserId()) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM DescriptorRecords WHERE id = :id");
        $p->execute([":id" => $this->id]);
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
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        $this->authorId = $r["authorId"];
        $this->title = $r["title"];
        $this->delta = $r["delta"];
    }
    public function serialize(): array
    {
        return ["authorId"=>$this->authorId, "title"=>$this->title, "delta" => $this->delta];
    }
}
