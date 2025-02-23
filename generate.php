<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Now</title>
    
    <style>
        body {
            color: white;
            background-image: url("image/back.jpg");
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: black;
            padding: 5px 10px;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            height: 50px;
        }
        .logoimg {
            width: 120px;
            height: auto;
        }
        nav {
            flex-grow: 1;
            display: flex;
            justify-content: center;
        }
        .items {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 15px;
        }
        .items:hover {
            color: rgb(220, 185, 185);
        }
        .content {
            padding: 100px 20px 50px;
            text-align: center;
        }
        h1 {
            color: orange;
        }
        p {
            font-size: 1.5rem;
        }
        .Generatebtn {
            background-color: orange;
            border-radius: 5px;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            color: white;
        }
        .Generatebtn:hover {
            background-color: rgb(187, 127, 15);
        }
        footer {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: black;
            position: relative;
            bottom: 0;
        }
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                height: auto;
                padding: 10px 0;
            }
            nav {
                margin-top: 5px;
            }
            .items {
                display: block;
                text-align: center;
            }
            .content p {
                width: 90%;
                margin: auto;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="homepage.html" class="logo"><img src="image/logo.png" class="logoimg"></a>
    <nav>
        <a href="homepage.html" class="items">Home</a>
    </nav>
</header>

<style>
    .input-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 70px;
    }
    .input-form input, .input-form button {
        margin: 10px;
        padding: 10px;
        font-size: 16px;
        width: 250px;
        text-align: center;
    }
    .input-form button {
        background-color: orange;
        color: black;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .input-form button:hover {
        background-color: rgb(187, 127, 15);
    }
</style>

<form action="generate.php" method="post">
    <div class="input-form">
        <input type="text" name="key1" placeholder="Enter your keyword">
        <input type="number" min="5" max="200" name="number" placeholder="Enter number of wanted names">
        <button type="submit" value="submit" name="submit">Submit</button>
    </div>
</form>
</body>
</html>
<?php
ob_start(); 

include 'database.php';
include 'functions.php';
require_once('tcpdf/tcpdf.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submit"])) {
        $key1 = trim($_POST["key1"] ?? '');
        $number = (int)($_POST["number"] ?? 0);

        if (!empty($key1) && $number >= 5 && $number <= 200) {
            generate($key1, $db, $number);

            $stmt = $db->prepare("SELECT id, word FROM users WHERE KEY1 = :key1 AND used = '0' ORDER BY created_at ASC LIMIT :num");
            $stmt->bindParam(":key1", $key1, PDO::PARAM_STR);
            $stmt->bindParam(":num", $number, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION["generated_words"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "<p style='color: red; text-align: center;'>Invalid input. Enter a valid keyword and a number between 5 and 200.</p>";
        }
    }

    if (isset($_POST["mark_used"], $_POST["selected_word"])) {
        $selected_id = (int)$_POST["selected_word"];

        $stmt = $db->prepare("SELECT word FROM users WHERE id = :id");
        $stmt->bindParam(":id", $selected_id, PDO::PARAM_INT);
        $stmt->execute();
        $selected_word = $stmt->fetchColumn();

        if ($selected_word) {
            $update_stmt = $db->prepare("UPDATE users SET used = '1' WHERE id = :id");
            $update_stmt->bindParam(":id", $selected_id, PDO::PARAM_INT);
            $update_stmt->execute();

            ob_clean(); // Clear any previous output

            $pdf = new TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetTitle('Selected Word');
            $pdf->AddPage();

            $html = file_get_contents("pdf.html");
            $html .= '<p style="font-size: 20px; text-align: center;">' . htmlspecialchars($selected_word) . '</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('Selected_Word.pdf', 'D');

            exit();
        }
    }
}

// Ensure that no output has been sent before the HTML section
if (!headers_sent() && !empty($_SESSION["generated_words"])) {
    echo "<h2 style='text-align: center;'>Generated Names:</h2>";
    echo "<form method='post' style='text-align: center;'>";
    echo "<div style='display: flex; flex-wrap: wrap; justify-content: center;'>";
    foreach ($_SESSION["generated_words"] as $row) {
        echo "<label style='background-color: white; color: black; padding: 10px; margin: 5px; border-radius: 5px; text-align: center;'>";
        echo "<input type='radio' name='selected_word' value='{$row['id']}'> " . htmlspecialchars($row['word']);
        echo "</label>";
    }
    echo "</div>";
    echo "<button type='submit' name='mark_used' style='margin-top: 10px; padding: 10px; background-color: orange; color: black; border: none; border-radius: 5px; cursor: pointer;'>Mark as Used</button>";
    echo "</form>";
}

$db = null;
?>







