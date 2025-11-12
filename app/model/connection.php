<?php
class Connexio {
    /**
     * Establish and return a PDO connection to the database.
     * @return PDO The PDO connection object.
     * @throws PDOException If there is an error in the connection.
     */
    public static function getConnection() {
        try {
            $conn = new PDO('mysql:host=localhost;dbname=Pt04_Alex_Ruiz', 'root', '');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Error de connexió: " . $e->getMessage());
        }
    }
}
?>
