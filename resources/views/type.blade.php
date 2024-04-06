@vite(['resources/js/app.js'])

<?php

//Session set
session_start();
if (!isset($_SESSION['textToCompare']))
{
    $_SESSION['textToCompare'] = '';
}

if (!isset($_SESSION['prevTime']))
{
    $_SESSION['prevTime'] = 0;
}

if (!isset($_SESSION['curTime']))
{
    $_SESSION['curTime'] = 0;
}
if (!isset($_SESSION['lastTrySpeed']))
{
    $_SESSION['lastTrySpeed'] = 0;
}

if(($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['timer'])))
{ // сетим prevTime и lastTrySpeed
    echo "<br>";
    echo "Text Length: " . strlen($_SESSION['textToCompare']) . "<br>";
    echo "Timer Value: " . $_POST['timer'] . "<br>";
    $_SESSION['prevTime'] = $_POST['timer'];
    $_SESSION['lastTrySpeed'] = sprintf("%.2f", strlen($_SESSION['textToCompare'])) / sprintf("%.2f",  $_POST['timer']);
}

if(($_SERVER['REQUEST_METHOD'] === "POST") && !empty($_POST['inputTextBox']))
{
    ?>
<script>
    alert('hello');
</script>

    <?php

    $_SESSION['curTime'] = 0;
    $_SESSION['textToCompare'] = $_POST['inputTextBox'];
    //+ js before /body
}

use Illuminate\Support\Facades\Http;
if(($_SERVER['REQUEST_METHOD'] === "POST") && isset($_POST['BibleButton']))
{
    $response = Http::get('https://bible-api.com/?random=verse');
    $response = json_decode($response, true);
    $stringResponse =  $response['verses']['0']['text'];
    $stringResponse = str_replace("’", "'", $stringResponse);
    $stringResponse = str_replace("‘", "'", $stringResponse);
    $stringResponse = str_replace("“", '"', $stringResponse);
    $stringResponse = str_replace("”", '"', $stringResponse);
    $stringResponse = str_replace(".", '. ', $stringResponse);
    $stringResponse = str_replace(",", ', ', $stringResponse);
    $stringResponse = str_replace(";", '; ', $stringResponse);
    $stringResponse = str_replace("  ", ' ', $stringResponse);
    $stringResponse = str_replace("—", '-', $stringResponse);
    echo "<div id='bibleResponse' style='display: none'>{$stringResponse}</div>";
    //+ js before /body
}

if(!empty($_SESSION['textToCompare']))
{
    echo "textToCompare: <div id='textToCompare'>{$_SESSION['textToCompare']}</div><br>";
    $lenOfCompareText = strlen($_SESSION['textToCompare']);
    echo "<div style='float: left';> Length of compare text:</div> <div id='lenOfFullText';> {$lenOfCompareText}</div> <br>";
}

else
{
    echo "text to compare is empty <br>";
}

$outputSpeed = sprintf("%.01f", $_SESSION['lastTrySpeed'] * 60);
echo "last try time: {$_SESSION['prevTime']} seconds <br>";
echo "last try speed: {$outputSpeed} s/m <br>";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Update Text</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #1a202c;
            color: #9ca3af;
            input[type="text"], textarea {

                background-color : #577393;
                border-color: #ef4444;
            }
            input[type="text"]:focus {

                background-color : #577393;
                border-color: #ef4444;
            }

            button {
                background-color : #577393;
                border-color: #ef4444;
            }
            input[type='submit'] {
                background-color : #577393;
                border-color: #ef4444;

            }
        }
    </style>
</head>
<body>
<div class="container-fluid d-flex flex-column align-items-center justify-content-center vh-100">

    <div>

        <form method="POST" action="{{ route('TypeTestController.store')}}">
        @csrf
            <input type="text" id="outputSpeed" name="outputSpeed" placeholder="outputSpeed" value="{{ strlen($_SESSION['textToCompare']) }}" readonly style="" >
            <lable for="timer"></lable>
            <input type="text" id="timer" name="timer" readonly style="">
            <input type="submit" id="submitTimeButton" name="submitTimeButton" style="visibility: hidden">
        </form>
    </div>

    <div>
        <form method="POST" action="{{ route('TypeTestController.type') }}">
        @csrf
            <label>
                <button name="BibleButton" id="BibleButton">
                    RandomBibleVerse
                </button>
            </label>
        </form>
        <form method="POST">
        @csrf
            <label>
                <input type="checkbox" title="should save text to savedtexts" name="checkbox" id="checkbox">
            </label>
            <label>
                <input type="text" name="inputTextBox" id="inputTextBox">
            </label>
            <label>
                <input type="submit" name="submitButton">
            </label>
        </form>
    </div>
    <div>
        <label for="textInput"></label><input type="text" id="textInput" class="form-control w-300 p-3 mw-100 " oninput="window.updateText()" style="width: 800px;">
    </div>
    <div>
        <br><br>
        <p id="output"></p>
    </div>
    <div>
        <?php
        if(isset($lenOfCompareText)) {
            for ($i = 0; $i < $lenOfCompareText; $i++) {
                if ($_SESSION['textToCompare'][$i] == " ") {
                    echo "<div style='float: left; background-color: #ffffff; opacity: .0;'>/</div> ";
                }
                echo "
            <div id='char{$i}' style='color: blue; float: left'>
                {$_SESSION['textToCompare'][$i]}
            </div>
            ";
            }
        }
        ?>
    </div>
    <div id="bool">
        bool
    </div>
</div>

<?php
if(($_SERVER['REQUEST_METHOD'] === "POST") && !empty($_POST['inputTextBox']))
{ //Запускаем таймер который каждую секунду апдейтит timer value, и output speed, фокусим на textInput
    ?>
<script>
    let timerCounter = 0;
    window.setInterval(myTimer, 1000);
    function myTimer() {
        timerCounter++;
        let fullTextLength = document.getElementById('lenOfFullText').innerText;

        document.getElementById('timer').value = timerCounter.toString();
        document.getElementById('outputSpeed').value = fullTextLength / timerCounter.toString() * 60;
    }

    document.getElementById('textInput').focus();
</script>
    <?php
}

if(($_SERVER['REQUEST_METHOD'] === "POST") && isset($_POST['BibleButton']))
{ // сетим inputTextBox содержимым BibleResponse (невидимым дивом который сетится через api)
    ?>
<script>
    bibleText = document.getElementById('bibleResponse').textContent;
    document.getElementById('inputTextBox').value = bibleText;
</script>
    <?php
}
?>


<ul>
{{--  выводим значени таблицы typeresults--}}
@foreach ($typeresults as $result)
        <li>{{ $result }}</li>
@endforeach
</ul>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
