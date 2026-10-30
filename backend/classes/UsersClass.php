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
 public function emailExists(string $email, int $excludeId = 0): bool
    {
        $dbh  = DB_conn::getConnection();
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

     public function updateUser(int $id, string $name, string $email, string $role, string $status, string $password = ''): bool
    {
        $dbh = DB_conn::getConnection();
        if ($password !== '') {
            $stmt = $dbh->prepare(
                "UPDATE users
                 SET name = ?, email = ?, role = ?, status = ?, password_hash = ?
                 WHERE id = ? AND deleted_at IS NULL"
            );
            return $stmt->execute([
                $name, $email, strtolower($role), strtolower($status),
                password_hash($password, PASSWORD_DEFAULT), $id
            ]);
        }
        $stmt = $dbh->prepare(
            "UPDATE users
             SET name = ?, email = ?, role = ?, status = ?
             WHERE id = ? AND deleted_at IS NULL"
        );
        return $stmt->execute([$name, $email, strtolower($role), strtolower($status), $id]);
    }
      public function updateStatus(int $id, string $status): bool
    {
        $dbh  = DB_conn::getConnection();
        $stmt = $dbh->prepare("UPDATE users SET status = ? WHERE id = ? AND deleted_at IS NULL");
        return $stmt->execute([strtolower($status), $id]);
    }
     public function deleteUser(int $id): bool
    {
        $dbh  = DB_conn::getConnection();
        $stmt = $dbh->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        return $stmt->execute([$id]) && $stmt->rowCount() > 0;
    }

}