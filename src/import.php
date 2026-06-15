<?php
session_start();
header('Content-type: text/html; charset=utf-8');
require "../config.php";
require_once 'functions.php';

include("class.login.php");
$log = new logmein();
// if not logged in
if ($_SESSION['loggedin'] == false) {
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/login.php");
} else
    $loggedin = 1;
// check if admin (only admin can change params)
if (!is_admin()) {
    echo "<h3>ΣΦΑΛΜΑ: Η πρόσβαση επιτρέπεται μόνο στο διαχειριστή...</h3>";
    echo "<a class='btn btn-info' href='admin.php'>Επιστροφή</a>";
    die();
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_set_charset($mysqlconnection, "utf8");

// Helper function to parse a date into day, month, year
function parse_date_custom($date_str)
{
    // Remove any whitespace
    $date_str = trim($date_str);
    // Try formats: d-m-Y, d/m/Y, Y-m-d
    $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $date_str);
        if ($dt && $dt->format($format) === $date_str) {
            return [
                'day' => (int) $dt->format('d'),
                'month' => (int) $dt->format('m'),
                'year' => (int) $dt->format('Y')
            ];
        }
    }
    // Fallback: try strtotime
    $timestamp = strtotime($date_str);
    if ($timestamp !== false) {
        return [
            'day' => (int) date('d', $timestamp),
            'month' => (int) date('m', $timestamp),
            'year' => (int) date('Y', $timestamp)
        ];
    }
    return false;
}

// Function to compute service time (eth, mhnes, hmeres)
// Uses 360 days per year, 30 days per month
function compute_service_time($hm_dior, $proyp, $av_endofyear)
{
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
    $total_days = $diff_days + (int) $proyp;

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
    <div class="container container-wide mt-5">
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Εισαγωγή Δεδομένων στη Βάση</h4>
            </div>
            <div class="card-body">
        <?php
        if (!isset($_POST['submit'])) {
            echo "<div class='alert alert-warning shadow-sm'><strong>ΠΡΟΣΟΧΗ:</strong> Η δυνατότητα αυτή διαγράφει όλα τα δεδομένα από τον πίνακα που θα επιλεγεί, πριν εισάγει σε αυτόν τα νέα.</div>";
            $rows = count_rows($mysqlconnection);
            echo "<p class='text-muted'>(Αριθμός εγγραφών που υπάρχουν στη Β.Δ.: Υπάλληλοι: <strong>" . $rows['emp'] . "</strong>, Σχολεία: <strong>" . $rows['sch'] . "</strong>, Δήμοι: <strong>" . $rows['dimos'] . "</strong>)</p>";
            echo "<div class='alert alert-danger shadow-sm'><strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη!</strong></div>";
            
            echo "<form enctype='multipart/form-data' action='import.php' method='post' class='mt-4'>";
            echo "<div class='form-group mb-4'>";
            echo "<label class='font-weight-bold'>Όνομα αρχείου προς εισαγωγή:</label>";
            echo "<input type='file' name='filename' class='form-control-file'>";
            echo "</div>";

            echo "<div class='form-group mb-4'>";
            echo "<label class='font-weight-bold'>Τύπος (πίνακας) δεδομένων:</label><br>";
            if ($av_type == 1) {
                echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type1' name='type' value='1' class='custom-control-input' required><label class='custom-control-label' for='type1'>Υπάλληλοι για απόσπαση (από αρχείο) <a href='../files/apo_employee.csv' class='text-muted'>(δείγμα)</a></label></div>";
                echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type6' name='type' value='6' class='custom-control-input' required><label class='custom-control-label' for='type6'>Υπάλληλοι για απόσπαση (από API Πρωτέα) <a href='#' class='text-muted'>(οδηγίες)</a></label></div>";
                echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type7' name='type' value='7' class='custom-control-input' required><label class='custom-control-label' for='type7'>Υπάλληλοι για απόσπαση (από API Πρωτέα) - ΕΝΗΜΕΡΩΣΗ</label></div>";
            } else if ($av_type == 2) {
                echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type4' name='type' value='4' class='custom-control-input' required><label class='custom-control-label' for='type4'>Υπάλληλοι για βελτίωση <a href='../files/apo_employee_velt.csv' class='text-muted'>(δείγμα)</a></label></div>";
            } else if ($av_type == 3) {
                echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type5' name='type' value='5' class='custom-control-input' required><label class='custom-control-label' for='type5'>Αναπληρωτές <a href='../files/apo_anapl.csv' class='text-muted'>(δείγμα)</a></label></div>";
            }
            echo "<div class='custom-control custom-radio mb-2'><input type='radio' id='type2' name='type' value='2' class='custom-control-input' required><label class='custom-control-label' for='type2'>Σχολεία <a href='../files/apo_school.csv' class='text-muted'>(δείγμα)</a></label></div>";
            echo "<div class='custom-control custom-radio mb-4'><input type='radio' id='type3' name='type' value='3' class='custom-control-input' required><label class='custom-control-label' for='type3'>Δήμοι <a href='../files/apo_dimos.csv' class='text-muted'>(δείγμα)</a></label></div>";
            echo "</div>";

            print "<button type='submit' name='submit' class='btn btn-custom btn-success mr-3'>Μεταφόρτωση</button>";
            echo "<a href='admin.php' class='btn btn-custom btn-info'>Επιστροφή</a>";
            echo "<div class='mt-3'><small class='text-muted'>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία. Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small></div>";
            echo "</form>";
            echo "</div></div></div></body></html>";
            exit;
        }



        // Check if it's an API import (type 6 or 7)
        if ($_POST['type'] == 6 || $_POST['type'] == 7) {
            // API Import for employees
            echo "<h4 class='mb-4'>Εισαγωγή υπαλλήλων από API Πρωτέα</h4>";

            // Check if cURL is available
            if (!function_exists('curl_version')) {
                echo "<div class='alert alert-danger'><strong>Σφάλμα:</strong> Η επέκταση cURL δεν είναι διαθέσιμη. Ενεργοποιήστε την στο php.ini</div>";
                echo "<a href='import.php' class='btn btn-custom btn-info'>Επιστροφή</a><br>";
                die();
            }

            // Get API configuration from apo_params
            $api_url = '';
            $api_key = '';
            $av_endofyear = '';

            $query = "SELECT pkey, pvalue FROM $av_params WHERE pkey IN ('api_url', 'api_key', 'av_endofyear')";
            $result = mysqli_query($mysqlconnection, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['pkey'] == 'api_url')
                    $api_url = $row['pvalue'];
                if ($row['pkey'] == 'api_key')
                    $api_key = $row['pvalue'];
                if ($row['pkey'] == 'av_endofyear')
                    $av_endofyear = $row['pvalue'];
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

                $is_update_only = ($_POST['type'] == 7);
                $inserted_count = 0;
                $updated_count = 0;
                $unchanged_count = 0;
                $detailed_summary = [];

                if (!$is_update_only) {
                    // Clear employee table before import
                    mysqli_query($mysqlconnection, "DELETE FROM $av_emp WHERE am <> '$av_admin'");
                    mysqli_query($mysqlconnection, "TRUNCATE $av_ait");
                }
                $tbl = $av_emp;

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
                    $am = $emp['am'] ?? $emp['teacher_code'] ?? '';
                    $afm = $emp['afm'] ?? '';
                    $hm_dior = $emp['hm_dior'] ?? $emp['appointment_date'] ?? '';
                    $proyp = $emp['proyp'] ?? $emp['previous_service_days'] ?? 0;

                    // Compute eth, mhnes, hmeres
                    list($eth, $mhnes, $hmeres) = compute_service_time($hm_dior, $proyp, $av_endofyear);

                    $am_trim = trim((string) $am);
                    $afm_trim = trim((string) $afm);

                    if ($is_update_only && ($am_trim === '' || $am_trim === '0' || $afm_trim === '' || $afm_trim === '0')) {
                        continue;
                    }

                    $am_val = (int) $am;
                    $afm_val = (int) $afm;

                    if ($is_update_only) {
                        // Check if employee exists by am and afm
                        $query_check = "SELECT id, name, surname, patrwnymo, klados, org, eth, mhnes, hmeres FROM $av_emp WHERE am = $am_val AND afm = $afm_val";
                        $res_check = mysqli_query($mysqlconnection, $query_check);

                        if ($res_check && mysqli_num_rows($res_check) > 0) {
                            // Employee exists, check for changes
                            $existing = mysqli_fetch_assoc($res_check);
                            
                            $changes_list = [];
                            if ($existing['name'] !== $name) {
                                $changes_list[] = "Όνομα: '{$existing['name']}' -> '{$name}'";
                            }
                            if ($existing['surname'] !== $surname) {
                                $changes_list[] = "Επώνυμο: '{$existing['surname']}' -> '{$surname}'";
                            }
                            if ($existing['patrwnymo'] !== $patrwnymo) {
                                $changes_list[] = "Πατρώνυμο: '{$existing['patrwnymo']}' -> '{$patrwnymo}'";
                            }
                            if ($existing['klados'] !== $klades_perigrafh) {
                                $changes_list[] = "Κλάδος: '{$existing['klados']}' -> '{$klades_perigrafh}'";
                            }
                            if ((int)$existing['org'] !== (int)$school_code) {
                                $changes_list[] = "Οργανική: '{$existing['org']}' -> '{$school_code}'";
                            }
                            if ((int)$existing['eth'] !== (int)$eth || (int)$existing['mhnes'] !== (int)$mhnes || (int)$existing['hmeres'] !== (int)$hmeres) {
                                $changes_list[] = "Υπηρεσία: '{$existing['eth']}ε, {$existing['mhnes']}μ, {$existing['hmeres']}η' -> '{$eth}ε, {$mhnes}μ, {$hmeres}η'";
                            }

                            $has_changes = count($changes_list) > 0;

                            if ($has_changes) {
                                $update = sprintf(
                                    "UPDATE $av_emp SET name='%s', surname='%s', patrwnymo='%s', klados='%s', org=%d, eth=%d, mhnes=%d, hmeres=%d WHERE id=%d",
                                    mysqli_real_escape_string($mysqlconnection, $name),
                                    mysqli_real_escape_string($mysqlconnection, $surname),
                                    mysqli_real_escape_string($mysqlconnection, $patrwnymo),
                                    mysqli_real_escape_string($mysqlconnection, $klades_perigrafh),
                                    (int) $school_code,
                                    (int) $eth,
                                    (int) $mhnes,
                                    (int) $hmeres,
                                    (int) $existing['id']
                                );
                                set_time_limit(480);
                                $ret = mysqli_query($mysqlconnection, $update);
                                if (!$ret) {
                                    $errors[] = "Σφάλμα κατά την ενημέρωση του υπαλλήλου $am_val: " . mysqli_error($mysqlconnection);
                                } else {
                                    $updated_count++;
                                    $num++;
                                    $detailed_summary[] = "Ενημερώθηκε: [ΑΜ: {$am_val}] {$surname} {$name} ({$klades_perigrafh}) - Αλλαγές: [" . implode(", ", $changes_list) . "]";
                                }
                            } else {
                                $unchanged_count++;
                            }
                        } else {
                            // Insert new employee since they don't exist
                            $import = sprintf(
                                "INSERT INTO $av_emp(name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres) VALUES('%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d)",
                                mysqli_real_escape_string($mysqlconnection, $name),
                                mysqli_real_escape_string($mysqlconnection, $surname),
                                mysqli_real_escape_string($mysqlconnection, $patrwnymo),
                                mysqli_real_escape_string($mysqlconnection, $klades_perigrafh),
                                $am_val,
                                $afm_val,
                                (int) $school_code,
                                (int) $eth,
                                (int) $mhnes,
                                (int) $hmeres
                            );
                            set_time_limit(480);
                            $ret = mysqli_query($mysqlconnection, $import);
                            if (!$ret) {
                                $errors[] = "Σφάλμα για υπάλληλο $am_val: " . mysqli_error($mysqlconnection);
                            } else {
                                $inserted_count++;
                                $num++;
                                $detailed_summary[] = "Προστέθηκε: [ΑΜ: {$am_val}] {$surname} {$name} ({$klades_perigrafh})";
                            }
                        }
                    } else {
                        // Original behavior: insert directly
                        $import = sprintf(
                            "INSERT INTO $av_emp(name, surname, patrwnymo, klados, am, afm, org, eth, mhnes, hmeres) VALUES('%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d)",
                            mysqli_real_escape_string($mysqlconnection, $name),
                            mysqli_real_escape_string($mysqlconnection, $surname),
                            mysqli_real_escape_string($mysqlconnection, $patrwnymo),
                            mysqli_real_escape_string($mysqlconnection, $klades_perigrafh),
                            $am_val,
                            $afm_val,
                            (int) $school_code,
                            (int) $eth,
                            (int) $mhnes,
                            (int) $hmeres
                        );

                        set_time_limit(480);
                        $ret = mysqli_query($mysqlconnection, $import);
                        if (!$ret) {
                            $errors[] = "Σφάλμα για υπάλληλο $am_val: " . mysqli_error($mysqlconnection);
                        } else {
                            $inserted_count++;
                            $num++;
                        }
                    }
                }

                if (count($errors) == 0) {
                    print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
                    if ($is_update_only) {
                        echo "Προστέθηκαν: $inserted_count υπάλληλοι, Ενημερώθηκαν: $updated_count, Δεν τροποποιήθηκαν: $unchanged_count.<br>";
                    } else {
                        echo "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>";
                    }
                } else {
                    echo "<h3>Παρουσιάστηκαν σφάλματα κατά την εισαγωγή</h3>";
                    foreach ($errors as $error) {
                        echo "<p>$error</p>";
                    }
                    if ($is_update_only) {
                        echo "Προστέθηκαν: $inserted_count υπάλληλοι, Ενημερώθηκαν: $updated_count, Δεν τροποποιήθηκαν: $unchanged_count.<br>";
                    } else {
                        echo "Επιτυχημένες εισαγωγές: $num<br>";
                    }
                }

                if ($is_update_only && count($detailed_summary) > 0) {
                    echo "<br><h5 class='mt-4'>Λεπτομέρειες Αλλαγών</h5>";
                    echo "<textarea class='form-control' rows='12' readonly style='font-family: monospace; font-size: 0.9rem; background-color: #f8f9fa; white-space: pre; overflow-y: scroll;'>";
                    echo htmlspecialchars(implode("\n", $detailed_summary), ENT_QUOTES, 'UTF-8');
                    echo "</textarea><br>";
                }
            }
        }
        //Upload File
        elseif (is_uploaded_file($_FILES['filename']['tmp_name'])) {
            echo "<h3>" . "To αρχείο " . $_FILES['filename']['name'] . " ανέβηκε με επιτυχία." . "</h3>";
            try {
                //Import uploaded file to Database
                $handle = fopen($_FILES['filename']['tmp_name'], "r");
                switch ($_POST['type']) {
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
                    if ($headers) {
                        $headers = 0;
                        continue;
                    }
                    // check if csv & table columns are equal
                    if (!$checked) {
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

                        if ($csvcols <> $tblcols) {
                            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
                            $ret = 0;
                            break;
                        } else
                            $checked = 1;
                    }

                    switch ($_POST['type']) {
                        // employees
                        case 1:
                            $import = "INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,eth,mhnes,hmeres) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]')";
                            break;
                        case 4:
                            $import = "INSERT into $av_emp(name,surname,patrwnymo,klados,am,afm,org,moria,entopiothta,synyphrethsh) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]',$data[7],'$data[8]','$data[9]')";
                            break;
                        case 5:
                            $import = "INSERT into $av_emp(name,surname,patrwnymo,klados,afm,seira,ada) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]')";
                            break;
                        // schools
                        case 2:
                            $import = "INSERT into $av_sch(name,kwdikos,dim,omada,inactive) values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]')";
                            break;
                        // dimoi
                        case 3:
                            $import = "INSERT into $av_dimos(name) values('$data[0]')";
                            break;
                    }
                    // set max execution time (for large files)
                    set_time_limit(480);
                    $ret = mysqli_query($mysqlconnection, $import);
                    $num++;
                }
            } catch (Exception $e) {
                echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
                echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
                echo "Μήνυμα λάθους: " . $e->getMessage() . "<br>";
            }

            fclose($handle);
            if ($ret) {
                print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
                echo "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>";
            } else {
                echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
                echo "Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.<br>";
                echo mysqli_error($mysqlconnection) ? "Μήνυμα λάθους:" . mysqli_error($mysqlconnection) : '';
                echo $num ? "Έγινε εισαγωγή $num εγγραφών στον πίνακα $tbl.<br>" : '';
            }
        } else {
            echo "<div class='alert alert-warning'>Δεν επιλέξατε αρχείο ή χρησιμοποιήσατε λάθος τύπο εισαγωγής.</div>";
        }

        echo "<br><a href='import.php' class='btn btn-custom btn-info mt-3'>Επιστροφή</a>";
        ?>
            </div>
        </div>
    </div>
</body>

</html>