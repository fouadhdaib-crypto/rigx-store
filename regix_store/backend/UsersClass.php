<?php
require_once __DIR__ . '/../config/DataBase.php';

class Users {
    private PDO $dbh;

    public function __construct(?PDO $dbh = null) {
        $this->dbh = $dbh ?? Database::getConnection();
    }

    public function register($name, $email, $password, $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->dbh->prepare(
            "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $email, $hash, $role]);
    }

    public function login($email, $password) {
        $stmt = $this->dbh->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return false;
    }
}