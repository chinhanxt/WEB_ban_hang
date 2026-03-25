<?php
class AccountModel
{
    private $conn;
    private $table_name = "account";

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureGoogleAuthSchema();
    }

    private function ensureGoogleAuthSchema()
    {
        $this->addColumnIfMissing('Email', "ALTER TABLE " . $this->table_name . " ADD COLUMN Email VARCHAR(100) DEFAULT NULL AFTER Username");
        $this->addColumnIfMissing('is_active', "ALTER TABLE " . $this->table_name . " ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER Role");
        $this->addColumnIfMissing('google_id', "ALTER TABLE " . $this->table_name . " ADD COLUMN google_id VARCHAR(191) DEFAULT NULL AFTER Password");
        $this->addColumnIfMissing('auth_provider', "ALTER TABLE " . $this->table_name . " ADD COLUMN auth_provider VARCHAR(30) NOT NULL DEFAULT 'local' AFTER google_id");
        $this->addColumnIfMissing('avatar', "ALTER TABLE " . $this->table_name . " ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER auth_provider");

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD UNIQUE KEY uniq_account_google_id (google_id)");
        } catch (Throwable $e) {
        }
    }

    private function addColumnIfMissing($column, $sql)
    {
        $query = "SELECT COUNT(*) FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':table' => $this->table_name,
            ':column' => $column,
        ]);

        if ((int)$stmt->fetchColumn() === 0) {
            $this->conn->exec($sql);
        }
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

    public function getAccountByGoogleId($googleId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE google_id = :google_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':google_id', $googleId);
        $stmt->execute();

        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        return $account ? (object)array_change_key_case($account, CASE_LOWER) : null;
    }

    public function save($username, $email, $fullname, $password, $role = 'user')
    {
        $query = "INSERT INTO " . $this->table_name . " (Username, Email, Fullname, Password, Role, auth_provider) 
                  VALUES (:username, :email, :fullname, :password, :role, :auth_provider)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $authProvider = 'local';
        $stmt->bindParam(':auth_provider', $authProvider);

        return $stmt->execute();
    }

    public function updateGoogleLink($accountId, $googleId, $avatar = null)
    {
        $query = "UPDATE " . $this->table_name . "
                  SET google_id = :google_id,
                      auth_provider = CASE WHEN auth_provider = 'local' THEN 'google_linked' ELSE auth_provider END,
                      avatar = COALESCE(:avatar, avatar)
                  WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':google_id' => $googleId,
            ':avatar' => $avatar ?: null,
            ':id' => $accountId,
        ]);
    }

    public function createGoogleAccount($username, $email, $fullname, $googleId, $avatar = null, $role = 'user')
    {
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table_name . " (Username, Email, Fullname, Password, Role, google_id, auth_provider, avatar)
                  VALUES (:username, :email, :fullname, :password, :role, :google_id, :auth_provider, :avatar)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':fullname' => $fullname,
            ':password' => $randomPassword,
            ':role' => $role,
            ':google_id' => $googleId,
            ':auth_provider' => 'google',
            ':avatar' => $avatar ?: null,
        ]);
    }

    public function generateUniqueUsername($baseName)
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '', strtolower($baseName));
        if ($slug === '') {
            $slug = 'googleuser';
        }

        if (strlen($slug) < 10) {
            $slug = str_pad($slug, 10, '0');
        }

        $candidate = substr($slug, 0, 24);
        $suffix = 1;

        while ($this->getAccountByUsername($candidate)) {
            $tail = (string)$suffix;
            $candidate = substr($slug, 0, max(1, 24 - strlen($tail))) . $tail;
            $suffix++;
        }

        return $candidate;
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
