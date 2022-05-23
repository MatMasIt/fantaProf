<?php


class Game implements CRUDL, ASerializable
{
    private int $id, $gameMasterId, $maxBettableProfs, $start, $end;
    private int $created, $lastEdit;
    private string $title, $description;
    private IdFieldList $professorIds, $descriptorIds;
    private PDO $database;
    private LoggedInUser $loggedInUser;
    function __construct(PDO $database, LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }
    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM Games WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = $id;
        $this->gameMasterId = (int) $r["gameMasterId"];
        $this->maxBettableProf = (int) $r["maxBettableProf"];
        $this->start = (int) $r["start"];
        $this->end = (int) $r["end"];
        $this->title = $r["title"];
        $this->description = $r["description"];
        $this->professorIds = new IdFieldList($this->database, new Professor($this->pdo, $this->loggedInUser, null));
        $this->professorIds->loadString($r["professorIds"]);
        $this->descriptorIds = new IdFieldList($this->database, new Descriptor($this->pdo, $this->loggedInUser, null));
        $this->professorIds->loadString($r["descriptorIds"]);
    }
    public function list(): array
    {
        $q = $this->database->prepare("SELECT * FROM Games");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new Game($this->database, $this->loggedInUser);
            $u->deserialize($result);
            $final[] = $u;
        }
        return $final;
    }
    public function delete(): void
    {
        if (!$this->loggedInUser) throw new AuthErrorException();
        if ($this->loggedInUser->getUser()->getId() != $this->id) throw new UnauthorizedException();
        $p = $this->database->prepare("DELETE FROM Games WHERE id = :id");
        $p->execute([":id" => $this->id]);
    }
    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        if (!$this->loggedInUser) throw new AuthErrorException();
        if ($this->loggedInUser->getUser()->getId() != $this->gameMasterId) throw new UnauthorizedException();
        $q = $this->database->prepare("UPDATE Games SET title=:title, gameMasteId=:gameMasterId, maxBettableProfs=:maxBettableProfs, start=:start, end=:end, professorIds=:professorIds, descriptorIds=:descriptorIds, lastEdite=:lastEdite WHERE id=:id");
        $q->execute([
            ":title" => $this->title,
            ":description" => $this->description,
            ":gameMasterId" => $this->gameMasterId,
            ":maxBettableProfs" => $this->maxBettableProfs,
            ":start" => $this->start,
            ":end" => $this->end,
            ":professorIds" => (string) $this->professorIds,
            ":descriptorIds" => (string) $this->descriptorIds,
            ":lastEdit" => $this->lastEdit,
            ":id" => $this->id
        ]);
    }
    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO Games(title, description, gameMasterId, maxBettableProfs, start, end, professorIds, descriptorIds, lastEdit, created) VALUES(:title, :description, :gameMasterId, :maxBettableProfs, :start, :end, :professorIds, :descriptorIds, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":title" => $this->title,
            ":description" => $this->description,
            ":gameMasterId" => $this->loggedInUser->getUser()->getId(),
            ":maxBettableProfs" => $this->maxBettableProfs,
            ":start" => $this->start,
            ":end" => $this->end,
            ":professorIds" => (string) $this->professorIds,
            ":descriptorIds" => (string) $this->descriptorIds,
            ":lastEdit" => $this->lastEdit,
            ":created" => $this->created
        ]);
        $this->passwordHash = $hash;
        $this->id = $this->database->lastInsertId();
    }
    public function deserialize(array $r): void
    {
        $this->title = $r["title"];
        $this->description = $r["description"];
        $this->gameMasterId = (int) $r["gameMasterId"];
        $this->maxBettableProfs = (int) $r["maxBettableProfs"];
        $this->start = (int) strtotime($r["start"]);
        $this->end = (int) strtotime($r["end"]);
        $this->professorIds = new IdFieldList($this->database, new Professor($this->database, $this->loggedInUser));
        $this->professorIds->setList($r["professorIds"]);
        $this->descriptorIds = new IdFieldList($this->database, new Descriptor($this->database, $this->loggedInUser));
        $this->professorIds->setList($r["descriptorIds"]);
    }
    public function serialize(): array
    {
        (User($this->database, $this->loggedInUser))->get((int) $r["gameMasterId"]);
        // exceptions will be thrown on error
        
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "gameMasterId" => $this->gameMasterId,
            "maxBettableProfs" => $this->maxBettableProfs,
            "start" => date("c", $this->start),
            "end" => date("c", $this->end),
            "professorIds" => $this->professorIds->getList(),
            "descriptorIds" => (string) $this->descriptorIds->getList(),
            "lastEdit" => date("c", $this->lastEdit),
            "created" => date("c", $this->created)
        ];
    }

    /**
     * Get the value of gameMasterId
     */
    public function getGameMasterId(): int
    {
        return $this->gameMasterId;
    }


    /**
     * Set the value of gameMasterId
     *
     * @return  self
     */
    public function setGameMasterId($gameMasterId): Game
    {
        $this->gameMasterId = $gameMasterId;

        return $this;
    }

    public function getGameMaster(): User
    {
        $u = new User($this->database, $this->loggedInUser);
        $u->get($this->gameMasterId);
        return $u;
    }

    public function setMaster(LoggedInUser $user): Game
    {
        $u =  $user->getUser();
        $this->gameMasterId = $u->getId();
        return $this;
    }
    /**
     * Get the value of maxBettableProfs
     */
    public function getMaxBettableProfs(): int
    {
        return $this->maxBettableProfs;
    }

    /**
     * Set the value of maxBettableProfs
     *
     * @return  self
     */
    public function setMaxBettableProfs($maxBettableProfs): Game
    {
        $this->maxBettableProfs = $maxBettableProfs;

        return $this;
    }

    /**
     * Get the value of start
     */
    public function getStart(): string
    {
        return date("c", $this->start);
    }

    /**
     * Set the value of start
     *
     * @return  self
     */
    public function setStart($start): Game
    {
        $this->start = strtotime($start);

        return $this;
    }

    /**
     * Get the value of created
     */
    public function getCreated(): string
    {
        return date("c", $this->created);
    }

    /**
     * Get the value of lastEdit
     */
    public function getLastEdit(): string
    {
        return date("c", $this->lastEdit);
    }

    /**
     * Get the value of end
     */
    public function getEnd(): string
    {
        return date("c", $this->end);
    }

    /**
     * Set the value of end
     *
     * @return  self
     */
    public function setEnd($end): Game
    {
        $this->end = strtotime($end);

        return $this;
    }
    public function getProfessorIds(): IdFieldList{
        return $this->professorIds;
    }
    public function setProfessorIds(IdFieldList $professorIds): void{
        $this->professorIds = $professorIds;
    }
    public function getDescriptorIds(): IdFieldList{
        return $this->professorIds;
    }
    public function setDescriptorids(IdFieldList $descriptorIds): void{
        $this->descriptorIds = $descriptorIds;
    }
}
