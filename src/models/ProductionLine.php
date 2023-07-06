<?php


class ProductionLine
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function updateProductionLineBySmartBox(array $data): int
    {
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $digitexSmartBox = $data["digitex_smart_box"];

        $sql = "UPDATE i6_prod_line
                SET Firstname = :firstName, Lastname = :lastName
                WHERE digitex = :digitexSmartBox";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':firstName', $firstName);
        $stmt->bindValue(':lastName', $lastName);
        $stmt->bindValue(':digitexSmartBox', $digitexSmartBox);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }

    public function getProductionLineBydigitex(string $digitex): array | string
    {
        $sql = "SELECT machine_id FROM prod__implantation WHERE smartbox = '$digitex'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            http_response_code(400);

            return "{}";
        }

        return $result;
    }
    public function updateProductionLineBydigitex(array $data): int
    {
        $digitex = $data["digitex"];
        $machine_ref = $data["machine_ref"];
        $prod_line = $data["prod_line"];

        $sql1 = "UPDATE prod__implantation SET smartbox = '' WHERE smartbox = '$digitex';";

        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->execute();
        $stmt1->closeCursor();

        $sql = "UPDATE prod__implantation SET prod_line_id = (SELECT `id` FROM `init__prod_line` WHERE `prod_line`='$prod_line'), smartbox = '$digitex' WHERE machine_id = '$machine_ref';";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }
    public function allProdlines(): array | string
    {
        $sql = "SELECT prod_line FROM init__prod_line";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        if (!$result) {
            http_response_code(400);

            return "{}";
        }

        return $result;
    }
    public function getmachine(array $data): int
    {
        $digitex = $data["digitex"];
        $machine_ref = $data["machine_ref"];
        $prod_line = $data["prod_line"];

        $sql = "SELECT * FROM prod__implantation WHERE machine_id = '$machine_ref'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        $machine = $result["machine_id"];

        if ($machine) {
            $this->updateProductionLineBydigitex($data);
        } else {
            $this->addProductionLineBydigitex($data);
        }

        return $stmt->rowCount();
    }
    public function addProductionLineBydigitex(array $data): int
    {
        $digitex = $data["digitex"];
        $machine_ref = $data["machine_ref"];
        $prod_line = $data["prod_line"];

        $sql = "INSERT INTO prod__implantation (prod_line_id, machine_id, smartbox, operator) VALUE ((SELECT `id` FROM `init__prod_line` WHERE `prod_line`='$prod_line'), '$machine_ref', '$digitex', 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }
}
