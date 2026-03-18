<?php
  session_start();
  header('Content-type: text/html; charset=utf-8'); 
  Require "../config.php";
  require_once 'functions.php';
  
  include("class.login.php");
  $log = new logmein();
  // if not logged in
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: " . dirname($_SERVER['PHP_SELF']) . "/login.php");
  }
  else
      $loggedin = 1;
  // check if admin (only admin can change params)
  if (!is_admin()){
    echo "<h3>ΣΦΑΛΜΑ: Η πρόσβαση επιτρέπεται μόνο στο διαχειριστή...</h3>";
    echo "<a class='btn btn-info' href='admin.php'>Επιστροφή</a>";
    die();
  }

  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_set_charset($mysqlconnection,"utf8");
  
// Helper function to parse a date into day, month, year
function parse_date_custom($date_str) {
    // Remove any whitespace
    $date_str = trim($date_str);
    // Try formats: d-m-Y, d/m/Y, Y-m-d
    $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $date_str);
        if ($dt && $dt->format($format) === $date_str) {
            return [
                'day' => (int)$dt->format('d'),
                'month' => (int)$dt->format('m'),
                'year' => (int)$dt->format('Y')
            ];
        }
    }
    // Fallback: try strtotime
    $timestamp = strtotime($date_str);
    if ($timestamp !== false) {
        return [
            'day' => (int)date('d', $timestamp),
            'month' => (int)date('m', $timestamp),
            'year' => (int)date('Y', $timestamp)
        ];
    }
    return false;
}

// Function to compute service time (eth, mhnes, hmeres)
// Uses 360 days per year, 30 days per month
function compute_service_time($hm_dior, $proyp, $av_endofyear) {
    $start = parse_date_custom($hm_dior);
    $end = parse_date_custom($av_endofyear);
    
    if (!$start || !$end) {
        return [0, 0, 0];
    }
    
    // Convert to total days using 360-day year, 30-day month
    $start_days = $start['year'] * 360 + ($start['month'] - 1) * 30 + $start['day'];
    $end_days = $end['year'] * 360 + ($end['month'] - 1) * 30 + $end['day'];
    
    $diff_days = $end_days - $start_days;
    if ($diff_days < 0) {
        $diff_days = 0;
    }
    
    // Add proyp (previous working days)
    $total_days = $diff_days + (int)$proyp;
    
    // Compute years (360 days), months (30 days), remaining days
    $years = floor($total_days / 360);
    $remaining = $total_days % 360;
    $months = floor($remaining / 30);
    $days = $remaining % 30;
    
    return [$years, $months, $days];
}
?>
<html>
  <?php require_once('head.php'); ?>
  <body>
  <div class="container">
<?php
if (!isset($_POST['submit']))
{
	echo "<h2> Εισαγωγή δεδομένων στη βάση δεδομένων </h2>";
  echo "ΠΡΟΣΟΧΗ: Η δυνατότητα αυτή διαγράφει όλα τα δεδομένα απο τον πίνακα που θα επιλεγεί, πριν εισάγει σε αυτόν τα νέα.<br><br>";
  $rows = count_rows($mysqlconnection);
  echo "(Αριθμός εγγραφών που υπάρχουν στη Β.Δ.: Υπάλληλοι: ".$rows['emp'].', Σχολεία: '.$rows['sch'].', Δήμοι: '.$rows['dimos'].')<br>';
	echo "<br><strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
    echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
    echo "Όνομα αρχείου προς εισαγωγή:<br />\n";
    echo "<input size='50' type='file' name='filename'><br />\n";
    echo "<br>Τύπος (πίνακας) δεδομένων:<br>";
    echo "<input type='radio' name='type' value='1'>Υπάλληλοι για απόσπαση&nbsp;<a href='../files/apo_employee.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='4'>Υπάλληλοι για βελτίωση&nbsp;<a href='../files/apo_employee_velt.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='5'>Αναπληρωτές&nbsp;<a href='../files/apo_anapl.csv'>(δείγμα)</a><br>";
    if ($av_type == 1) {
        echo "<input type='radio' name='type' value='6'>Υπάλληλοι από API Πρωτέα&nbsp;<a href='#'>(οδηγίες)</a><br>";
    }
    echo "<input type='radio' name='type' value='2'>Σχολεία&nbsp;<a href='../files/apo_school.csv'>(δείγμα)</a><br>";
    echo "<input type='radio' name='type' value='3' >Δήμοι&nbsp;<a href='../files/apo_dimos.csv'>(δείγμα)</a><br>";
    print "<input type='submit' name='submit' class='btn btn-success' value='Μεταφόρτωση'></form>";
    echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
    echo "</form>";
    echo "<br><br>";
    echo "<a href='admin.php' class='btn btn-info'>Επιστροφή</a>";
	exit;
}


    
    // Check if it's an API import (type 6)
    if ($_POST['type'] == 6) {
        // API Import for employees
        echo "<h3>Εισαγωγή υπαλλήλων από API Πρωτέα</h3>";
        
        // Check if cURL is available
        if (!function_exists('curl_version')) {
            echo "<h3>Σφάλμα: Η επέκταση cURL δεν είναι διαθέσιμη. Ενεργοποιήστε την στο php.ini</h3>";
            echo "<a href='import.php'>Επιστροφή</a><br>";
            die();
        }
        
        // Get API configuration from apo_params
        $api_url = '';
        $api_key = '';
        $av_endofyear = '';
        
        $query = "SELECT pkey, pvalue FROM $av_params WHERE pkey IN ('api_url', 'api_key', 'av_endofyear')";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['pkey'] == 'api_url') $api_url = $row['pvalue'];
            if ($row['pkey'] == 'api_key') $api_key = $row['pvalue'];
            if ($row['pkey'] == 'av_endofyear') $av_endofyear = $row['pvalue'];
        }
        
        if (empty($api_url) || empty($api_key) || empty($av_endofyear)) {
            echo "<h3>Σφάλμα: Δεν έχουν ρυθμιστεί οι παράμετροι API (api_url, api_key, av_endofyear) στον πίνακα apo_params</h3>";
            echo "<a href='params.php'>Μετάβαση στις ρυθμίσεις</a><br>";
        } else {
            // Prepare to fetch data from API
            $headers = [
                'X-API-Key: ' . $api_key,
                'User-Agent: insomnia/2023.5.8'
            ];
            
            // Fetch employees with status 1 or 3
            $employee_url = $api_url . '/employee?filter=status,eq,1&status,eq,3';
            // print_r($headers);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $employee_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $employee_response = curl_exec($ch);
            $employee_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                echo "<h3>Σφάλμα κατά τη σύνδεση στο API υπαλλήλων: " . curl_error($ch) . "</h3>";
                curl_close($ch);
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            curl_close($ch);
            
            if ($employee_http_code != 200) {
                echo "<h3>Σφάλμα API: HTTP κωδικός (employee) " . $employee_http_code . "</h3>";
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            
            $employees = json_decode($employee_response, true);
            if (isset($employees['records'])) {
                $employees = $employees['records'];
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<h3>Σφάλμα: Μη έγκυρη JSON απάντηση από API υπαλλήλων</h3>";
                die();
            }
            
            // Fetch klados data
            $klados_url = $api_url . '/klados';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $klados_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $klados_response = curl_exec($ch);
            $klados_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                echo "<h3>Σφάλμα κατά τη σύνδεση στο API κλάδων: " . curl_error($ch) . "</h3>";
                curl_close($ch);
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            curl_close($ch);
            
            if ($klados_http_code != 200) {
                echo "<h3>Σφάλμα API: HTTP κωδικός (klados) " . $klados_http_code . "</h3>";
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            
            $klados_data = json_decode($klados_response, true);
            if (isset($klados_data['records'])) {
                $klados_data = $klados_data['records'];
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<h3>Σφάλμα: Μη έγκυρη JSON απάντηση από API κλάδων</h3>";
                die();
            }
            
            // Fetch schools data
            $schools_url = $api_url . '/school';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $schools_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $schools_response = curl_exec($ch);
            $schools_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                echo "<h3>Σφάλμα κατά τη σύνδεση στο API σχολείων: " . curl_error($ch) . "</h3>";
                curl_close($ch);
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            curl_close($ch);
            
            if ($schools_http_code != 200) {
                echo "<h3>Σφάλμα API: HTTP κωδικός (school) " . $schools_http_code . "</h3>";
                echo "<a href='import.php'>Επιστροφή</a><br>";
                die();
            }
            
            $schools_data = json_decode($schools_response, true);
            if (isset($schools_data['records'])) {
                $schools_data = $schools_data['records'];
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<h3>Σφάλμα: Μη έγκυρη JSON απάντηση από API σχολείων</h3>";
                die();
            }
            
            // Reorganize klados and schools data for quick lookup
            $klados_map = [];
            foreach ($klados_data as $k) {
                // Assuming klados API returns: id, perigrafh
                $klados_map[$k['id']] = $k['perigrafh'] ?? $k['name'] ?? '';
            }
            
            $schools_map = [];
            foreach ($schools_data as $s) {
                // Assuming schools API returns: id, code
                $schools_map[$s['id']] = $s['code'] ?? '';
            }

            // Clear employee table before import
            mysqli_query($mysqlconnection, "DELETE FROM $av_emp WHERE am <> '$av_admin'");
            $tbl = $av_emp;
            mysqli_query($mysqlconnection, "TRUNCATE $av_ait");
            
            // Process employees
            $num = 0;
            $errors = [];
            
            foreach ($employees as $emp) {
                // Get klados description from klados_map
                $klados_id = $emp['klados'] ?? $emp['klados_id'] ?? 0;
                $klades_perigrafh = isset($klados_map[$klados_id]) ? $klados_map[$klados_id] : '';
                
                // Get school code from schools_map
                $school_id = $emp['sx_organikhs'] ?? $emp['school_id'] ?? 0;
                $school_code = isset($schools_map[$school_id]) ? $schools_map[$school_id] : 0;
                
                // Extract employee data
                $name = $emp['name'] ?? '';
                $surname = $emp['surname'] ?? '';
                $patrwnymo = $emp['patrwnymo'] ?? '';
                $am = $emp['am'] ?? $emp['teacher_code'] ?? 0;
                $afm = $emp['afm'] ?? 0;
                $hm_dior = $emp['hm_dior'] ?? $emp['appointment_date'] ?? '';
                $proyp = $emp['proyp'] ?? $emp['previous_service_days'] ?? 0;
                
                // Compute eth, mhnes, hmeres
                list($eth, $mhnes, $hmeres) = compute_service_time($hm_dior, $proyp, $av_endofyear);
                
                // Insert into database
                $import = sprintf(
                    "INSERT INTO $av_emp(name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres) VALUES('%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d)",
                    mysqli_real_escape_string($mysqlconnection, $name),
                    mysqli_real_escape_string($mysqlconnection, $surname),
                    mysqli_real_escape_string($mysqlconnection, $patrwnymo),
                    mysqli_real_escape_string($mysqlconnection, $klades_perigrafh),
                    (int)$am,
                    (int)$afm,
                    (int)$school_code,
                    (int)$eth,
                    (int)$mhnes,
                    (int)$hmeres
                );
                
                set_time_limit(480);
                $ret = mysqli_query($mysqlconnection, $import);
                if (!$ret) {
                    $errors[] = "Σφάλμα για υπάλληλο $am: " . mysqli_error($mysqlconnection);
                } else {
                    $num++;
                }
            }
            
            if (count($errors) == 0) {
                print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
                echo "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>";
            } else {
                echo "<h3>Παρουσιάστηκαν σφάλματα κατά την εισαγωγή</h3>";
                foreach ($errors as $error) {
                    echo "<p>$error</p>";
                }
                echo "Επιτυχημένες εισαγωγές: $num<br>";
            }
        }
    }
    //Upload File
    elseif (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";
        try {
            //Import uploaded file to Database
            $handle = fopen($_FILES['filename']['tmp_name'], "r");
            switch ($_POST['type'])
            {
                case 1:
                case 4:
                case 5:
                    mysqli_query($mysqlconnection, "DELETE FROM $av_emp WHERE am <> '$av_admin'");
                    $tbl = $av_emp;
                    mysqli_query($mysqlconnection, "TRUNCATE $av_ait");
                    break;
                case 2:
                    mysqli_query($mysqlconnection, "TRUNCATE $av_sch");
                    $tbl = $av_sch;
                    break;
                case 3:
                    mysqli_query($mysqlconnection, "TRUNCATE $av_dimos");
                    $tbl = $av_dimos;
                    break;
            }
            $num = 0;
            $checked = 0;
            $headers = 1;
            
                
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    // convert to utf8 using mb_helper
                    $data = array_map('mb_helper', $data);
                    // skip header line
                    if ($headers){
                        $headers = 0;
                        continue;
                    }
                    // check if csv & table columns are equal
                    if (!$checked)
                    {
                        $csvcols = count($data);
                        
                        switch ($_POST['type']) {
                        case 1:
                        case 4:
                            $tblcols = 10;
                            break;
                        case 5:
                            $tblcols = 7;
                            break;
                        case 2:
                            $tblcols = 5;
                            break;
                        case 3:
                            $tblcols = 1;
                            break;
                        }

                        if ($csvcols <> $tblcols)
                        {
                            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
                            $ret = 0;
                            break;
                        }
                        else
                            $checked = 1;
                    }

                    switch ($_POST['type']){
                        // employees
                        case 1:
                        $import="INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,eth,mhnes,hmeres) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]')";
                        break;
                        case 4:
                        $import="INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,moria,entopiothta,synyphrethsh) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]',$data[7],'$data[8]','$data[9]')";
                        break;
                        case 5:
                        $import="INSERT into $av_emp(name,surname,patrwnymo,klados,afm,seira,ada) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]')";
                        break;
                        // schools
                        case 2:
                        $import="INSERT into $av_sch(name,kwdikos,dim,omada,inactive) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]')";
                        break;
                        // dimoi
                        case 3:
                        $import="INSERT into $av_dimos(name) values('$data[0]')";
                        break;
                    }
                    // set max execution time (for large files)
                    set_time_limit (480);
                    $ret = mysqli_query($mysqlconnection, $import);
                    $num++;
                }
        }
        catch (Exception $e) {
            echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
            echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
            echo "Μήνυμα λάθους: " . $e->getMessage() . "<br>";
        }

        fclose($handle);
        if ($ret){
            print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
            echo "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>";
        }
        else
        {
            echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
            echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
            echo mysqli_error($mysqlconnection) ? "Μήνυμα λάθους:".mysqli_error($mysqlconnection) : '';
            echo $num ? "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>" : '';
        }
    }
    else {
        echo "Δεν επιλέξατε αρχείο ή χρησιμοποιήσατε λάθος τύπο εισαγωγής<br><br>";
    }
                
    echo "<a href='import.php'>Επιστροφή</a>";
?>
</div>
</body>
</html>
	