<?php


class SNAICard implements CRUDL, ASerializable
{
    private int $id;
    private int $userId, $gameId, $bettedProfsId ;

    function __construct(PDO $database, ?LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }
    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM SNAICards WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = $id;
        $this->userId = $r["userId"];
        $this->gameId = (int) $r["gameId"];
        $this->bettedProfsId = $r["bettedProfsId"];
    }
    public function list(): array
    {
        $q = $this->database->prepare("SELECT * FROM SNAICards");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new SNAICard($this->database, $this->loggedInUser);
            $u->deserialize($result);
            $final[] = $u;
        }
        return $final;
    }
    public function delete(): void
    {
        if (!$this->loggedInUser) throw new AuthErrorException();
        if ($this->loggedInUser->getUser()->getId() != $this->authorId) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM SNAICards WHERE id = :id");
        $p->execute([":id" => $this->id]);
        
        //cascade delete descriptor from all games
        
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO SNAICard(gameId, userId, bettedProfsId) VALUES(:gameId, :userId, :bettedProfsId)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":gameId" => $this->gameId,
            ":userId" => $this->userId,
            ":bettedProfsId" => $this->bettedProfsId
        ]);
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
