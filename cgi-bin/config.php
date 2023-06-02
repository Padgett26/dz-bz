<?php
session_start();

include "../globalFunctions.php";

$db = db_dzbz();
$dbsfai = db_sfi();

date_default_timezone_set('America/Chicago');
$time = time();
$domain = "dz-bz.com";

$visitingIP = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);

// *** Log out ***
if (filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_STRING) == 'yep') {
    destroySession();
    setcookie("staySignedIn", '', $time - 1209600, "/", $domain, 0);
}

// *** Sign in ***
$loginErr = "x";
if (filter_input(INPUT_POST, 'login', FILTER_SANITIZE_NUMBER_INT) == "1") {
    $email = (filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) ? strtolower(
            filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : '0';
    $login1stmt = $db->prepare("SELECT id,salt FROM users WHERE email = ?");
    $login1stmt->execute(array(
            $email
    ));
    $login1row = $login1stmt->fetch();
    $salt = $login1row['salt'];
    $checkId = (isset($login1row['id']) && $login1row['id'] > 0) ? $login1row['id'] : '0';
    $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING);
    $hidepwd = hash('sha512', ($salt . $pwd), FALSE);
    $login2stmt = $db->prepare(
            "SELECT id FROM users WHERE email = ? AND password = ? && accessLevel >= ?");
    $login2stmt->execute(array(
            $email,
            $hidepwd,
            "1"
    ));
    $login2row = $login2stmt->fetch();
    if ($login2row['id']) {
        $x = $login2row['id'];
        $_SESSION['myId'] = $x;
        setcookie("staySignedIn", $_SESSION['myId'], $time + 1209600, "/",
                $domain, 0); // set for 14 days
        $lastUpdate = $db->prepare(
                "UPDATE users SET lastLogin = ? WHERE id = ?");
        $lastUpdate->execute(array(
                $time,
                $x
        ));
    } else {
        $loginErr = "Your email / password combination isn't correct, or you haven't verified your email address.";
    }
}

if (filter_input(INPUT_POST, 'orderUp', FILTER_SANITIZE_NUMBER_INT) >= 1) {
    $t = filter_input(INPUT_POST, 'orderUp', FILTER_SANITIZE_NUMBER_INT);
    $n = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $a = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $p = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $e = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
    $stmt1 = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt1->execute(array(
            $e
    ));
    $row1 = $stmt1->fetch();
    if ($row1[0] >= 1) {
        $currentCus = $row1[0];
        $stmt2 = $db->prepare(
                "UPDATE customers SET name = ?, address = ?, phone = ? WHERE id = ?");
        $stmt2->execute(array(
                $n,
                $a,
                $p,
                $currentCus
        ));
    } else {
        $stmt2 = $db->prepare("INSERT INTO customers VALUES(NULL,?,?,?,?,?)");
        $stmt2->execute(array(
                $time,
                $n,
                $a,
                $p,
                $e
        ));
        $stmt3 = $db->prepare(
                "SELECT id FROM customers WHERE timeAdded = ? && email = ? ORDER BY id DESC LIMIT 1");
        $stmt3->execute(array(
                $time,
                $e
        ));
        $row3 = $stmt3->fetch();
        $currentCus = $row3[0];
    }
    foreach ($_POST as $name => $q) {
        if (preg_match("/^product([1-9][0-9]*)$/", $name, $match)) {
            $product = filter_var($match[1], FILTER_SANITIZE_NUMBER_INT);
            $stmt4 = $db->prepare(
                    "INSERT INTO shoppingCart VALUES(NULL,?,?,?,?,'0')");
            $stmt4->execute(array(
                    $currentCus,
                    $time,
                    $product,
                    $q
            ));
        }
    }
    foreach ($_COOKIE as $name => $value) {
        if (preg_match("/^cart([1-9][0-9]*)$/", $name, $match)) {
            setcookie("cart$match[1]", '', $time - 1209600, "/", ".dz-bz.com", 0);
        }
    }
    $orderIn = 1;
    $total = 0;
    $mess = "<html><head></head><body>DZ-BZ order<br>from:<br>$n<br>$a<br>$p<br>$e<br><br>";
    foreach ($_POST as $name => $q) {
        if (preg_match("/^product([1-9][0-9]*)$/", $name, $match)) {
            $product = filter_var($match[1], FILTER_SANITIZE_NUMBER_INT);
            $stmt4 = $db->prepare("SELECT * FROM product WHERE id = ?");
            $stmt4->execute(array(
                    $product
            ));
            $stmt4Row = $stmt4->fetch();
            $mess .= "Product: " . $stmt4Row['name'] . "<br>Size: " .
                    $stmt4Row['size'] . "<br>Price: " . $stmt4Row['price'] .
                    "<br>Qty: $q<br><br>";
            $total = ($total + ($q * $stmt4Row['price']));
        }
    }
    $mess .= "Total price: $$total + $10 shipping = $" . ($total + 10) . "<br>";
    $mess .= "</body></html>";
    $message = wordwrap($mess, 70);
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
    $headers .= "From: $n <$e>" . "\r\n";
    mail('dgp@dz-bz.com', "DZ-BZ Order From $n", $message, $headers);
}

$inCart = array();

if (filter_input(INPUT_POST, 'qty', FILTER_SANITIZE_NUMBER_INT) >= 1) {
    $p = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_NUMBER_INT);
    $q = filter_input(INPUT_POST, 'qty', FILTER_SANITIZE_NUMBER_INT);
    setcookie("cart$p", $q, $time + 1209600, "/", ".dz-bz.com", 0); // set for
                                                                    // 14 days
    $inCart[] = $p;
}

foreach ($_COOKIE as $name => $value) {
    if (preg_match("/^cart([1-9][0-9]*)$/", $name, $match)) {
        $product = filter_var($match[1], FILTER_SANITIZE_NUMBER_INT);
        $inCart[] = $product;
    }
}

$inCartCount = count($inCart);
