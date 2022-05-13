<?php
class LoggedInUser // the only authorized handle for authenticated users
{
    public const NOT_AUTHENTICATED = 0;
    private User $user;
    private PDO $database;
    public static function getSession(PDO $database, string $token): LoggedInUser
    {
        $q = $database->prepare("SELECT id FROM Users WHERE token=:token");
        $q->execute([":token" => $token]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new AuthErrorException();
        $user = new User($database, null);
        $user->get($r["id"]);
        return new LoggedInUser($user, $database);
    }
    public function getUser(): User
    {
        return $this->user;
    }
    private function __construct(User $user, PDO $database)
    {
        $this->database = $database;
        $this->user = $user;
    }
    public static function signUp(PDO $database, string $username, $name,  string $surname, string $email, string $classe, string $password, string $imgUrl): User
    {
        $user = new User($database, null);
        $user->create([
            "username" => $username,
            "name" => $name,
            "surname" => $surname,
            "classe" => $classe,
            "email" => $email,
            "imgUrl" => $imgUrl,
            "password" => $password
        ]);
        return $user;
    }
    public static function logInWithEmail(PDO $database, string $email, string $password): LoggedInUser
    {
        $q = $database->prepare("SELECT * FROM Users WHERE email=:email");
        $q->execute([":email" => $email]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new AuthErrorException();
        $user = new User($database, null);
        $user->get($r["id"]);
        if (!$user->isPassword($password)) throw new AuthErrorException();
        $user->generateToken();
        return new LoggedInUser($user, $database);
    }
    public static function loginInWithUsername(PDO $database, string $username, string $password): LoggedInUser
    {
        $q = $database->prepare("SELECT * FROM Users WHERE username=:username");
        $q->execute([":username" => $username]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) throw new AuthErrorException();
        $user = new User($database, null);
        $user->get($r["id"]);
        if (!$user->isPassword($password)) throw new AuthErrorException();
        $user->generateToken();
        return new LoggedInUser($user, $database);
    }
    public function changePassword(string $newPassword, string $oldPassword): bool
    {
        if ($this->user->isPassword($oldPassword)) {
            $this->user->setPassword($newPassword);
            $this->user->update();
            return true;
        }
        return false;
    }
    public function delete(string $password): bool
    {
        if ($this->user->isPassword($password)) {
            $this->user->delete();
            return true;
        }
        return false;
    }
    public function isSystemMaster()
    {
        return $this->id == 9;
    }
}
