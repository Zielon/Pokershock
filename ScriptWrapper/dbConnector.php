<?php
/*
 * Singleton sedign patter class to create connection with data base
 */

function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

class DbConnector {
 
    private static $instance = null;
    private $connMySQLi;
    private $connMySQL;
    private $_host  = "localhost:3036";
    private $_username = "18646393_0000001";
    private $_password = "bazadanych1992";

    private function __construct()
    {
        $this->connMySQLi = new mysqli($this->_host, $this->_username, $this->_password, $this->_username);
        $this->connMySQL = mysql_connect($this->_host, $this->_username, $this->_password);
        mysql_select_db('18646393_0000001');
    }
    
    function __destruct() {

    }

    public static function getInstance()
    {
        if(!self::$instance)
        {
          self::$instance = new DbConnector();
        }
        return self::$instance;
    }

    //Connection getter
    public function getConnection()
    {
        return $this->connMySQL;
    }

    public function getConnectionMySqli()
    {
        return $this->connMySQLi;
    }

    //Get a result set from a statement
    public function sendRequest($sql){
        $retval = mysql_query($sql, $this->connMySQL);
        return $retval;
    }

    //Get a result set from a prepared statement
    public function sendRequestWithParams($sql, $types = null, $params = null){
        $stmt = $this->connMySQLi->prepare($sql);
        if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
        }
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        return $data;
    }
}?>