<?php
// url: http://www.emirplicanic.com/php/simple-phpmysql-authentication-class

//For security reasons, don't display any errors or warnings. Comment out in DEV.
error_reporting(0);
//start session
session_start();
class logmein {
    var $hostname_logon;
    var $database_logon;
    var $username_logon;
    var $password_logon;
    var $theConnection;
    var $user_table;
    protected $_prop;
    
    public function get_vars()
    {
      include '../config.php';
      $this->hostname_logon = $db_host;
      $this->database_logon = $db_name;
      $this->username_logon = $db_user;
      $this->password_logon = $db_password;
      $this->user_table = $av_emp;
    }
    	
    //table fields
    var $user_column = 'am';     //USERNAME column (value MUST be valid email)
    var $pass_column = 'afm';      //PASSWORD column
 
    //encryption
    var $encrypt = false;       //set to true to use md5 encryption for the password
 
    //connect to database
    function dbconnect(){
        $this->get_vars();
        $this->theConnection = mysqli_connect($this->hostname_logon, $this->username_logon, $this->password_logon, $this->database_logon) or die ('Unable to connect to the database');
        return;
    }
 
    //login function
    function login($table, $username, $password, $extraval){
        global $av_extra, $av_extra_name, $av_extra_label;
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
        $extraquery = $av_extra ? " AND ".$av_extra_name."='".$extraval."'" : '';
        $qry = "SELECT * FROM $this->user_table WHERE am='$username' AND $this->pass_column='$password'".$extraquery;
        $res = $this->qry($qry);
        
        $rows_emp = mysqli_num_rows($res);
        if ($rows_emp>0)
        {
            //execute login via qry function that prevents MySQL injections
            //$result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='?' AND ".$this->pass_column." = '?';" , $username, $password);
            $row = mysqli_fetch_assoc($res);
            if($row != "Error"){
                if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
                    //register sessions
                    //you can add additional sessions here if needed
                    $_SESSION['loggedin'] = $row[$this->pass_column];
                    $_SESSION['user'] = $row[$this->user_column];
                    //$_SESSION['userid'] = $row['userid'];
                    //userlevel session is optional. Use it if you have different user levels
                    //$_SESSION['userlevel'] = $row[$this->user_level];
                    $result = $this->qry("UPDATE ".$this->user_table." SET lastlogin=now() WHERE ".$this->user_column."='".$username."'");
                    return true;
                } else {
                    session_destroy();
                    return false;
                }
            } else {
                return false;
            }
        }
        else
            return false;
    }
 
    //prevent injection
    function qry($query) {
      $this->dbconnect();
      /*
      $args  = func_get_args();
      $query = array_shift($args);
      $query = str_replace("?", "%s", $query);

      $args = mysqli_real_escape_string($this->theConnection, $args[0]);
      $args = array($args);
      print_r($args);
      array_unshift($args,$query);
      
      $query = call_user_func_array('sprintf', $args);*/
      $result = mysqli_query($this->theConnection, $query) or die(mysqli_error($this->theConnection));
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
    function logincheck($logincode, $user_table, $pass_column, $user_column, $extra_value = NULL){
        global $av_extra, $av_extra_name;
        //connect to DB
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
        //execute query
        //$result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = '?';" , $logincode);
        $extra_query = $av_extra ? " AND ".$av_extra_name." = '".$extra_value."'" : '';
        $result = mysqli_query($this->theConnection, "SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = ".$logincode.$extra_query);
        
        $rownum = mysqli_num_rows($result);
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
        global $av_extra, $av_extra_name, $av_extra_label;
        //connect to DB
        $this->dbconnect();
        echo '
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'" autocomplete="off">
<div class="form-group">
    <label for="username">Αριθμός Μητρώου Εκπ/κού</label>
    <input type="text" class="form-control" id="username" name="username">
  </div>
  <div class="form-group">
    <label for="password">Α.Φ.Μ. Εκπ/κού</label>
    <input type="password" class="form-control" name="password" id="password">
  </div>';
if ($av_extra) {
    echo '<div class="form-group">
    <label for="">'.$av_extra_label.'</label>
    <input type="password" class="form-control" name="'.$av_extra_name.'" id="'.$av_extra_name.'">
  </div>';
}
echo '<input name="action" id="action" value="login" type="hidden">
<button type="submit" class="btn btn-primary">Είσοδος στο σύστημα</button>
</form>
';
    }
}
?>