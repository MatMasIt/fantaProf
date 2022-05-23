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
    public function list($preserialize = true): array
    {
        $q = $this->database->prepare("SELECT id FROM DescriptorRecords");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new DescriptorRecord($this->database, $this->loggedInUser);
            $u->deserialize((int) $result["id"]);
            if(!$preserialize) $final[] = $u;
            else $final[] = $u->serialize();
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
        $snai = new SNAICard($this->database, $this->loggedInUser);
        $snai->get($this->SNAICardId);
        if ($this->loggedInUser->getUser()->getId() != $snai->getUserId()) throw new UnauthorizedException();
        if (!$this->id) throw new NotFoundException();
        $q = $this->database->prepare("UPDATE DescriptorRecords SET SNAICardId=:SNAICardId, profId=:profId, descriptorId=:descriptorId, instant=:instant, comment=:comment WHERE id=:id");
        $q->execute([
            ":SNAICardId" => $this->SNAICardId,
            ":profId" => $this->profId,
            ":descriptorId" => $this->descriptorId,
            ":instant" => $this->instant,
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO DescriptorRecords(SNAICardId, profId, descriptorId, instant) VALUES(:SNAICardId, :profId, :descriptorId, :instant)");
        $q->execute([
            ":SNAICardId" => $this->SNAICardId,
            ":profId" => $this->profId,
            ":descriptorId" => $this->descriptorId,
            ":instant" => $this->instant,
        ]);
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        (SNAICard($this->database, $this->loggedInUser))->get((int) $r["SNAICardId"]);
        (Professor($this->database, $this->loggedInUser))->get((int) $r["profId"]);
        (Descriptor($this->database, $this->loggedInUser))->get((int) $r["descriptorId"]);

        // if they do not exist, an exception will be thrown
        $this->SNAICardId = (int) $r["SNAICardId"];
        $this->profId = (int) $r["profId"];
        $this->descriptorId = (int) $r["descriptorId"];
        $this->instant = (int) strtotime($r["instant"]);
    }
    public function serialize(): array
    {
        return [
            "id" => $this->id,
            "SNAICardId" => $this->SNAICardId,
            "profId" => $this->profId, 
            "descriptorId" => $this->descriptorId,
            "instant" => date("c", $this->instant),
        ];
    }
}
