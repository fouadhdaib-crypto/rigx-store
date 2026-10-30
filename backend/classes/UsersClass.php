<?php
require_once __DIR__ . '/../config/db.php';
class UsersClass {

public function register($name, $email, $password, $role) {
        $dbh = DB_conn::getConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $inst = $dbh->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        return $inst->execute([$name, $email, $hashedPassword, $role]);
    }

    public function login($email, $password) {
        try {
            $dbh = DB_conn::getConnection(); 
            $stmt = $dbh->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);   
            if ($user && password_verify($password, $user['password_hash'])) {
                return $user; 
            }
            return false;  
        } catch (PDOException $e) {
            return false;
        }
    }
    public function addUser(string $name, string $email, string $password, string $role, string $status): bool
    {
        $dbh  = DB_conn::getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $dbh->prepare(
            "INSERT INTO users (name, email, password_hash, role, status, address_line1)
             VALUES (?, ?, ?, ?, ?, '')"
        );
        return $stmt->execute([$name, $email, $hash, strtolower($role), strtolower($status)]);
    }

}