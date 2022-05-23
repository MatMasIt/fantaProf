<?php


class User implements CRUDL, ASerializable
{
    private int $id;
    private string $username, $name, $surname, $classe, $email, $imgUrl;
    private string $passwordHash;
    private string $token;
    private int $created, $lastEdit;

    private PDO $database;
    private ?LoggedInUser $loggedInUser; // if this is not null we are operating on another user
    function __construct(PDO $database, ?LoggedInUser $loggedInUser)
    {
        $this->database = $database;
        $this->loggedInUser = $loggedInUser;
    }

    public function get(int $id): void
    {
        $q = $this->database->prepare("SELECT * FROM Users WHERE id=:id");
        $q->execute([":id" => $id]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new NotFoundException();
        $this->id = $id;
        $this->username = $r["username"];
        $this->name = $r["name"];
        $this->surname = $r["surname"];
        $this->classe = $r["classe"];
        $this->email = $r["email"];
        $this->passwordHash = $r["passwordHash"];
        $this->imgUrl = $r["imgUrl"];
        $this->token = $r["token"];
        $this->created = $r["created"];
        $this->lastEdit = $r["lastEdit"];
    }
    public function list($preserialize = true): array
    {
        $q = $this->database->prepare("SELECT id FROM Users");
        $q->execute();
        $re = $q->fetchAll(PDO::FETCH_ASSOC);
        $final = [];
        foreach ($re as $result) {
            $u = new User($this->database, $this->loggedInUser);
            $u->get((int) $result["id"]);
            if(!$preserialize) $final[] = $u;
            else $final[] = $u->serialize();
        }
        return $final;
    }

    public function delete(): void
    {
        if ($this->loggedInUser) throw new AuthErrorException();
        $p = $this->database->prepare("DELETE FROM Users WHERE id = :id");
        $p->execute([":id" => $this->id]);
    }

    public function create(array $data): void
    {
        $this->deserialize($data);
        $hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $q = $this->database->prepare("INSERT INTO Users(username, name, surname, classe, email, passwordHash, token, imgUrl, lastEdit, created) VALUES(:username, :name, :surname, :classe, :email, :passwordHash, :token, :imgUrl, :lastEdit, :created)");
        $this->created = time();
        $this->lastEdit = $this->created;
        $q->execute([
            ":username" => $this->username,
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":classe" => $this->classe,
            ":email" => $this->email,
            ":passwordHash" => $hash,
            ":token" => bin2hex(random_bytes(16)),
            ":imgUrl" => $this->imgUrl,
            ":lastEdit" => $this->lastEdit,
            ":created" => $this->created
        ]);
        $this->passwordHash = $hash;
        $this->id = $this->database->lastInsertId();
    }

    public function deserialize(array $r, $database = false): void
    {
        $this->username = $r["username"];
        $this->name = $r["name"];
        $this->surname = $r["surname"];
        $this->classe = $r["classe"];
        $this->email = $r["email"];
        $this->imgUrl = $r["imgUrl"];
    }

    public function serialize(): array
    {
        $a =  [
            "id" => $this->id,
            "username" => $this->username,
            "name" => $this->name,
            "surname" => $this->surname,
            "classe" => $this->classe,
            "email" => $this->email,
            "imgUrl" => $this->imgUrl,
            "lastEdit" =>  date("c", $this->lastEdit),
            "created" => date("c", $this->created)
        ];
        if (!$this->loggedInUser) $a["token"] = $this->token;
        return $a;
    }


    public function update(): void
    {
        if (!$this->id) throw new NotFoundException();
        $q = $this->database->prepare("UPDATE Users SET username=:username, name=:name, surname=:surname, classe=:classe, email=:email, passwordHash=:passwordHash, imgUrl=:imgUrl, token=:token, lastEdit=:lastEdit WHERE id=:id");
        $q->execute([
            ":username" => $this->username,
            ":name" => $this->name,
            ":surname" => $this->surname,
            ":classe" => $this->classe,
            ":email" => $this->email,
            ":passwordHash" => $this->passwordHash,
            ":imgUrl" => $this->imgUrl,
            ":token" => $this->token,
            ":lastEdit" => time(),
            ":id" => $this->id
        ]);
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username): User
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of surname
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * Set the value of surname
     *
     * @return  self
     */
    public function setSurname($surname): User
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the value of classe
     */
    public function getClasse(): string
    {
        return $this->classe;
    }

    /**
     * Set the value of classe
     *
     * @return  self
     */
    public function setClasse($classe): User
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of imgUrl
     */
    public function getImgUrl(): string
    {
        return $this->imgUrl;
    }

    /**
     * Set the value of imgUrl
     *
     * @return  self
     */
    public function setImgUrl(string $imgUrl): void
    {
        $this->imgUrl = $imgUrl;
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

    public function setPassword(string $password): void
    {
        if (!$this->loggedInUser) { // only if null
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        } else throw new UnauthorizedException();
    }

    public function isPassword(string $password): bool
    {
        if (!$this->loggedInUser) { // only if null
            return password_verify($password, $this->passwordHash);
        } else throw new UnauthorizedException();
    }
    public function generateToken(): string
    {
        if ($this->loggedInUser) throw new UnauthorizedException();
        $this->token = bin2hex(random_bytes(16));
        $this->update();
        return $this->token;
    }
    public function getToken(): string
    {
        if ($this->loggedInUser) throw new UnauthorizedException();
        return $this->token;
    }
}
