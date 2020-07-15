<?php

require_once 'parameters.php';

class DatabaseService
{
    private $connection;
    public $loggedUser;

    private function createConnection() {
        $this->connection = new mysqli(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
        
        // Check connection
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    private function closeConnection() {
        $this->connection->close();
    }

    public function login($username, $password) {
        $this->createConnection();

        $sql = "SELECT * FROM user WHERE username = '" . $username . "' AND password = '" . $password . "'";
        $result = $this->connection->query($sql);

        if ($result->num_rows == 0) {
            header("HTTP/1.1 401 Unauthorized");
            die('{ "success": false, "error": "User not found" }');
        }

        if ($result->num_rows > 1) {
            header("HTTP/1.1 401 Unauthorized");
            die('{ "success": false, "error": "More than one user with this username-password combination" }');
        }

        $this->loggedUser = $result->fetch_assoc();

        if ($this->loggedUser) {
            $sql = "UPDATE user SET lastlogin = CURRENT_TIMESTAMP WHERE username = '" . $username . "' AND password = '" . $password . "'";
            $this->connection->query($sql);
        }

        $this->closeConnection();
        return $this->loggedUser;
    }

    public function addBooking($reference, $status, $creationUserId, $creationDate, $creationJson) {
        $this->createConnection();

        $sql = "
        INSERT INTO booking (
            reference, 
            status, 
            creation_user_id, 
            creation_date, 
            creation_json
        ) VALUES (
            '" . $reference . "',
            '" . $status . "',
            " . $creationUserId . ",
            '" . $creationDate . "',
            '" . $creationJson . "'
        )";
        
        $result = $this->connection->query($sql);

        if (!$result) {
            $this->logError($sql);
        }

        $this->closeConnection();
        return $result;
    }

    public function cancelBooking($reference, $status, $cancellationUserId, $cancellationJson) {
        $this->createConnection();

        $sql = "
        UPDATE booking SET 
            status = '" . $status . "', 
            cancellation_user_id = " . $cancellationUserId . ", 
            cancellation_date = CURRENT_TIMESTAMP, 
            cancellation_json = '" . $cancellationJson . "'
        WHERE 
            reference = '" . $reference . "'";

        $result = $this->connection->query($sql);

        if (!$result) {
            $this->logError($sql);
        }

        $this->closeConnection();
        return $result;
    }

    public function getUserBookings($userId) {
        $this->createConnection();

        $sql = "SELECT reference FROM booking WHERE creation_user_id = " . $userId;
        $result = $this->connection->query($sql);

        if (!$result) {
            $this->logError($sql);
            return [];
        }
        
        $bookingList = []; 
        while ($row = $result->fetch_assoc()){
            $bookingList[] = $row['reference'];
        }

        $this->closeConnection();
        return $bookingList;
    }

    public function logError($query) {
        $sql = "
        INSERT INTO errorlog (
            user_id, 
            date, 
            query
        ) VALUES (
            " . $this->loggedUser['id'] . ",
            CURRENT_TIMESTAMP,
            \"" . $query . "\"
        )";
        $result = $this->connection->query($sql);
    }
}

?>