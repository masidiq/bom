<?php

require_once("Rest.inc.php");

class API extends REST {

    public $data = "";

    const DB_SERVER = "127.0.0.1";
    const DB_USER = "root";
    const DB_PASSWORD = "";
    const DB = "bom";

    private $db = NULL;
    private $mysqli = NULL;

    public function __construct() {
        parent::__construct();    // Init parent contructor
        $this->dbConnect();     // Initiate Database connection
    }

    /*
     *  Connect to Database
     */

    private function dbConnect() {
        $this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
    }

    /*
     * Dynmically call the method based on the query string
     */

    public function processApi() {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404); // If the method not exist with in this class "Page not found".
    }
    
    private function tes(){
        $query = "insert into boms (name) values('KZL')";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
        $this->response($result, 200); // send user details
    }

    // Part
    private function parts() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT * FROM parts";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function part() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "SELECT distinct o.id, o.itemCode, o.description FROM parts o where o.id=$id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();
                $this->response($this->json($result), 200); // send user details
            }
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function createPart() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $model = json_decode(file_get_contents("php://input"), true);
        $column_names = array('itemCode', 'description');
        $keys = array_keys($model);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) {
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $model[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO parts (" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($model)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $customer);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }

    private function updatePart() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $model = json_decode(file_get_contents("php://input"), true);
        $id = (int) $model['id'];
        $column_names = array('itemCode', 'description');
        $keys = array_keys($model['part']);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If key does not exist, insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $model['part'][$desired_key];
            }
            $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }
        $query = "UPDATE parts SET " . trim($columns, ',') . " WHERE id=$id";
        if (!empty($model)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Part " . $id . " Updated Successfully.", "data" => $model);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // "No Content" status
    }

    private function deletePart() {
        if ($this->get_request_method() != "DELETE") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "DELETE FROM parts WHERE id = $id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // If no records "No Content" status
    }

    // End Part
    //Bom
    private function boms() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT * FROM boms";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function bom() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "SELECT distinct * FROM boms o where o.id=$id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();
                $this->response($this->json($result), 200); // send user details
            }
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function createBom() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        
        $model = json_decode(file_get_contents("php://input"), true);
        $column_names = array('name', 'customer','created','bom_list_number','project_number','product_number');
        $keys = array_keys($model);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) {
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $model[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO boms (" . trim($columns, ',') . ",last_update) VALUES(" . trim($values, ',') . ",'".date('Y-m-d')."')";
        if (!empty($model)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $bom_id = $this->mysqli->insert_id;
            $list = $model['partList'];
            foreach ($list as $item) {
                
                $query2 = "INSERT INTO bom_item (bom_id,part_id,qty) values ('".$bom_id."','".$item['id']."','".$item['qty']."')";
                $r2 = $this->mysqli->query($query2) or die($this->mysqli->error . __LINE__);
            }
            
            
            
            
            $success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $model);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }

    private function updateBom() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $model = json_decode(file_get_contents("php://input"), true);
        $id = (int) $model['id'];
        $column_names = array('name', 'customer','created','bom_list_number','project_number','product_number');
        $keys = array_keys($model['bom']);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If key does not exist, insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $model['bom'][$desired_key];
            }
            $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }
        $query = "UPDATE boms SET " . trim($columns, ',') . " WHERE id=$id";
        if (!empty($model)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Bom " . $id . " Updated Successfully.", "data" => $model);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // "No Content" status
    }

    private function deleteBom() {
        if ($this->get_request_method() != "DELETE") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "DELETE FROM boms WHERE id = $id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // If no records "No Content" status
    }

    //end bom

    /*
     * 	Encode array into JSON
     */

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

// Initiiate Library

$api = new API;
$api->processApi();
?>