<?php
class AccountModel
{
    private $conn;
    private $table_name = "account";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAccountByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        return $account ? (object)array_change_key_case($account, CASE_LOWER) : null;
    }

    public function getAccountByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        return $account ? (object)array_change_key_case($account, CASE_LOWER) : null;
    }

    public function save($username, $email, $fullname, $password, $role = 'user')
    {
        $query = "INSERT INTO " . $this->table_name . " (Username, Email, Fullname, Password, Role) 
                  VALUES (:username, :email, :fullname, :password, :role)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);

        return $stmt->execute();
    }

    public function getAccounts()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];
        foreach ($accounts as $account) {
            $results[] = (object)array_change_key_case($account, CASE_LOWER);
        }
        return $results;
    }

    public function toggleActive($id, $currentStatus)
    {
        $newStatus = $currentStatus ? 0 : 1;
        $query = "UPDATE " . $this->table_name . " SET is_active = :status WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function resetPassword($id, $newPassword)
    {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE " . $this->table_name . " SET Password = :password WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
