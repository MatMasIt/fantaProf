<?php


class SNAICard implements CRUDL, ASerializable
{
    private int $id;
    private int $userId, $gameId;
    private IdFieldList $bettedprofsIds;
    private PDO $database;
    private LoggedInUser $loggedInUser;
    function __construct(PDO $database, LoggedInUser $loggedInUser)
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
        $this->id = (int) $id;
        $this->userId = (int) $r["userId"];
        $this->gameId = (int) $r["gameId"];
        $this->bettedProfsId = new IdFieldList(new Professor($this->database, $this->loggedInUser, null));
        $this->bettedProfsId->loadString($r["bettedProfIds"]);
    }
    public function list($preserialize = true): array
    {
        $q = $this->database->prepare("SELECT id FROM SNAICards");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new SNAICard($this->database, $this->loggedInUser);
            $u->get((int) $result["id"]);
            if (!$preserialize) $final[] = $u;
            else $final[] = $u->serialize();
        }
        return $final;
    }
    public function delete(): void
    {
        if ($this->loggedInUser->getUser()->getId() != $this->userId) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM SNAICards WHERE id = :id");
        $p->execute([":id" => $this->id]);

        //cascade delete descriptor from all games

    }
    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        if ($this->loggedInUser->getUser()->getId() != $this->userId) throw new UnauthorizedException();
        $q = $this->database->prepare("UPDATE SNAICards SET gameId=:gameId, userId=:userId, bettedProfsId=:bettedProfsId WHERE id=:id");
        $q->execute([
            ":gameId" => $this->gameId,
            ":userId" => $this->userId,
            ":bettedProfsId" => (string) $this->bettedProfsId,
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $q = $this->database->prepare("INSERT INTO SNAICards(gameId, userId, bettedProfIds) VALUES(:gameId, :userId, :bettedProfIds)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":gameId" => $this->gameId,
            ":userId" => $this->loggedInUser->getUser()->getId(),
            ":bettedProfIds" => (string) $this->bettedProfsId
        ]);
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        (new Game($this->database, $this->loggedInUser))->get((int) $r["gameId"]);
        (new User($this->database, $this->loggedInUser))->get((int) $r["userId"]);

        $this->gameId = (int) $r["gameId"];
        $this->userId = (int) $r["userId"];
        $this->bettedProfsId = new IdFieldList(new Professor($this->database, $this->loggedInUser));
        $this->bettedProfsId->setList($r["bettedProfsId"]);
        //$this->forceMaxProfConstraint(); Constraints? suure
    }
    public function serialize(): array
    {
        return [
            "id" => $this->id,
            "gameId" => $this->gameId,
            "userId" => $this->userId,
            "bettedProfsId" => $this->bettedProfsId->getList()
        ];
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getGameId(): int
    {
        return $this->gameId;
    }
    public function getBettedprofsIds(): IdFieldList
    {
        return $this->bettedProfsId;
    }
    public function setBettedprofsIds(IdFieldList $bettedProfsId): void
    {
        $this->bettedProfsId = $bettedProfsId;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    private function forceMaxProfConstraint(): void
    {
        $game = new Game($this->database, $this->loggedInUser);
        if ($this->bettedProfsId->size() > $game->getMaxBettableProfs()) {
            $a = $this->bettedProfsId->getList();
            $this->bettedProfsId->setList(array_slice($a, 0, $game->getMaxBettableProfs));
        }
    }
}
