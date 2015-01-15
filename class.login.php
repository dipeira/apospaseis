<?php
// url: http://www.emirplicanic.com/php/simple-phpmysql-authentication-class

//For security reasons, don't display any errors or warnings. Comment out in DEV.
//include 'config.php';
error_reporting(0);
//start session
session_start();
class logmein {
    var $hostname_logon;
    var $database_logon;
    var $username_logon;
    var $password_logon;
	var $user_table;
    protected $_prop;
    public function get_vars()
    {
        include 'config.php';
        $this->hostname_logon = $db_host;
        $this->database_logon = $db_name;
        $this->username_logon = $db_user;
        $this->password_logon = $db_password;
		$this->user_table = $av_emp;
    }
    	
    //table fields
    //var $user_table = 'apo_amafm';          //Users table name
    //var $user_table = 'apo_employee';          //Users table name
    var $user_column = 'am';     //USERNAME column (value MUST be valid email)
    var $pass_column = 'afm';      //PASSWORD column
    //var $user_level = 'userlevel';      //(optional) userlevel column
 
    //encryption
    var $encrypt = false;       //set to true to use md5 encryption for the password
 
    //connect to database
    function dbconnect(){
        $this->get_vars();
        $connections = mysql_connect($this->hostname_logon, $this->username_logon, $this->password_logon) or die ('Unabale to connect to the database');
        mysql_select_db($this->database_logon) or die ('Unable to select database!');
        return;
    }
 
    //login function
    function login($table, $username, $password){
        //connect to DB
        $this->dbconnect();
        //make sure table name is set
        if($this->user_table == ""){
            $this->user_table = $table;
        }
        //check if encryption is used
        if($this->encrypt == true){
            $password = md5($password);
        }
		// sugarvag: leading zero?
		if (strlen($password) == 8)
			$password = '0'.$password;
        // sugarvag: check if employee can apply
        $qry = "SELECT * FROM apo_employee WHERE am=".$username;
        $res = $this->qry($qry);
        //echo $qry;
        //echo "<form><input type='submit' value='qqq' /></form>";
        $rows_emp = mysql_num_rows($res);
        if ($rows_emp>0)
        {
            //execute login via qry function that prevents MySQL injections
            $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='?' AND ".$this->pass_column." = '?';" , $username, $password);
            $row=mysql_fetch_assoc($result);
            if($row != "Error"){
                if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
                    //register sessions
                    //you can add additional sessions here if needed
                    $_SESSION['loggedin'] = $row[$this->pass_column];
                    $_SESSION['user'] = $row[$this->user_column];
                    //$_SESSION['userid'] = $row['userid'];
                    //userlevel session is optional. Use it if you have different user levels
                    //$_SESSION['userlevel'] = $row[$this->user_level];
                    $result = $this->qry("UPDATE ".$this->user_table." SET lastlogin=now()WHERE ".$this->user_column."='?';" , $username);
                    //$row=mysql_fetch_assoc($result);
                    return true;
                }else{
                    session_destroy();
                    return false;
                }
            }else{
                return false;
            }
        }
        else
            return false;
 
    }
 
    //prevent injection
    function qry($query) {
      $this->dbconnect();
      $args  = func_get_args();
      $query = array_shift($args);
      $query = str_replace("?", "%s", $query);
      $args  = array_map('mysql_real_escape_string', $args);
      array_unshift($args,$query);
      $query = call_user_func_array('sprintf',$args);
      $result = mysql_query($query) or die(mysql_error());
          if($result){
            return $result;
          }else{
             $error = "Error";
             return $result;
          }
    }
 
    //logout function
    function logout(){
        session_start();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        return;
    }
 
    //check if loggedin
    function logincheck($logincode, $user_table, $pass_column, $user_column){
        //conect to DB
        $this->dbconnect();
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
            //exectue query
            $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = '?';" , $logincode);
            $rownum = mysql_num_rows($result);
            //return true if logged in and false if not
            if($row != "Error"){
                if($rownum > 0){
                    return true;
                }else{
                    return false;
                }
            }
    }
 
    //login form
    function loginform($formname, $formclass, $formaction){
        //conect to DB
        $this->dbconnect();
        echo '
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'" autocomplete="off">
<table>
<tr><td><div><label for="username">Αριθμός Μητρώου Εκπ/κού</label></td>
<td><input name="username" id="username" type="text" ></div></td></tr>
<tr><td><div><label for="password">Α.Φ.Μ. Εκπ/κού</label></td>
<td><input name="password" id="password" type="password" ></div></td></tr>
<tr><td colspan=2><input name="action" id="action" value="login" type="hidden">
<div>
<center><input name="submit" id="submit" value="Είσοδος στο σύστημα" type="submit"></center></div></td></tr></table>
</form>
';
    }
}
?>