<?php
$lifetime = 60 * 60 * 24 * 30;
session_start();
setcookie(session_name(), session_id(), time() + $lifetime);
define("ADMINKEY", "viewAdminAll");
$datenbank = "data.sqlite";
// Datenbank-Datei erstellen
if (!file_exists($datenbank)) {
    $db = new PDO('sqlite:' . $datenbank);
    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
    $db->exec("CREATE TABLE stunden(
  id INTEGER PRIMARY KEY,
  user CHAR(255),
  week INTEGER,
  lessonid INTEGER,
  hasTime BOOLEAN)");
} else {
    // Verbindung
    $db = new PDO('sqlite:' . $datenbank);
    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
}

if (isset($_POST["action"]) && $_POST["action"] == "save") {
    $_SESSION["user"] = $_POST["user"];
    foreach ($_POST["data"] as $stunde) {
        $select = $db->query("SELECT `id` FROM stunden WHERE `user`='" . $_POST["user"] . "' AND `week`=" . $stunde["woche"] . " AND `lessonid`=" . $stunde["stunde"]);

        //$select->bindValue(':user', );
        //$select->bindValue(':week', );
        //$select->bindValue(':lessonid', );
        //$res = $select->execute();
        //print_r($select->errorInfo());
        $row = $select->fetch(PDO::FETCH_ASSOC);
        //var_dump($row);
        //echo $_POST["user"];
        //echo $stunde["woche"];
        //echo $stunde["stunde"];

        if (!$row) {
            $insert = $db->prepare("INSERT INTO stunden 
            (`user`, `week`, `lessonid`, `hasTime`)
            VALUES (:user, :week, :lessonid, :hasTime)");
            $insert->bindValue(':user', $_POST["user"]);
            $insert->bindValue(':week', $stunde["woche"]);
            $insert->bindValue(':lessonid', $stunde["stunde"]);
            $insert->bindValue(':hasTime', $stunde["zeit"]);

            if (!$insert->execute()) {
                print_r($insert->errorInfo());
                die();
            }
        } else {

            $update = $db->prepare("UPDATE stunden 
            SET `hasTime`=:hasTime WHERE `id`=:id");
            $update->bindValue(':id', $row["id"]);
            $update->bindValue(':hasTime', $stunde["zeit"]);

            if (!$update->execute()) {
                print_r($update->errorInfo());
                die();
            }
        }
    }
    die();
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Zeitumfrage</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .stundenplan table {
            text-align: center;
        }

        .notime {
            background-color: #ff5e5e;
            color: #820000;
        }

        .notime:before {
            content: "Nein";
        }

        .yestime {
            background-color: #5eff9b;
            color: #00821a;
        }

        .yestime:before {
            content: "Ja";
        }

        .choose {
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container mt-3">
        <div class="jumbotron">
            <h1>Zeitumfrage <?php if (isset($_GET[ADMINKEY])) {
                                echo "Administratoransicht";
                            } ?></h1>
            <?php if (!isset($_GET[ADMINKEY])) { ?>
                <p class="lead">Bitte gib an, wer du bist und wann zu Zeit hast, um beim Dreh dabei zu sein.</p>
                <input class="form-control" autocomplete="off" type="text" id="nameInput" placeholder="<?php echo (isset($_SESSION["user"]) ? $_SESSION["user"] : "Vor- und Nachname"); ?>" value="<?php echo (isset($_SESSION["user"]) ? $_SESSION["user"] : "");  ?>">
            <?php } ?>
            <div class="my-4">
                Klicke alle Stunden an, in denen Du Zeit hättest, etwas für die AGM zu drehen.
            </div>
            <div id="stundenplaene">
                <?php $wochen = array(
                    "2. Woche nach den Ferien (14.06.2021 - 18.06.2021)",
                    "3. Woche nach den Ferien (21.06.2021 - 25.06.2021)",
                    "4. Woche nach den Ferien (28.06.2021 - 02.07.2021)",
                    "5. Woche nach den Ferien (05.07.2021 - 09.07.2021)",
                    "6. Woche nach den Ferien (12.07.2021 - 16.07.2021)",
                    "Vorletzte Schulwoche (19.07.2021 - 23.07.2021)",
                );
                $wochencounter = 0;
                foreach ($wochen as $nummer => $woche) {
                    $wochencounter++; ?>


                    <div class="stundenplan">
                        <h3><?php echo $woche; ?></h3>
                        <br>
                        <table class="table table-hover table-sm table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Zeit</th>
                                    <th>Montag</th>
                                    <th>Dienstag</th>
                                    <th>Mittwoch</th>
                                    <th>Donnerstag</th>
                                    <th>Freitag</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $zeiten = array(
                                    "07:45 - 08:30", "08:30 - 09:15", "09:15 - 10:00",
                                    "PAUSE",
                                    "10:25 - 11:10", "11:10 - 11:55", "11:55 - 12:40",
                                    "PAUSE",
                                    "13:25 - 14:10", "14:10 - 14:55",
                                    "PAUSE",
                                    "15:05 - 15:50", "15:50 - 16:35",
                                );
                                $stundecounter = 0;
                                $stundencounter = 0;
                                foreach ($zeiten as $zeit) {
                                    if ($zeit != "PAUSE") {
                                        $stundecounter++;
                                        echo "<tr>
                                        <td>$stundecounter. Stunde</span></td>
                                        <td>$zeit</span></td>";
                                        for ($i = 0; $i < 5; $i++) {
                                            $stundencounter++;



                                            if (!isset($_GET[ADMINKEY])) {
                                                $class = "notime";
                                                if (isset($_SESSION["user"])) {
                                                    $select = $db->query("SELECT `hasTime` FROM stunden WHERE `user`='" . $_SESSION["user"] . "' AND `week`=" . $wochencounter . " AND `lessonid`=" . $stundencounter);
                                                    $row = $select->fetch(PDO::FETCH_ASSOC);

                                                    if (isset($row["hasTime"])) {
                                                        if ($row["hasTime"] == "true") {
                                                            $class = "yestime";
                                                        }
                                                    }
                                                }

                                                echo "<td data-wocheid='$wochencounter' data-id='$stundencounter' class='choose $class'></td>";
                                            } else {

                                                $select = $db->query("SELECT `user` FROM stunden WHERE `week`=" . $wochencounter . " AND `lessonid`=" . $stundencounter . " AND hasTime = 'true'");
                                                $content = "";
                                                $first = true;
                                                while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                                                    if ($first) {
                                                        $first = false;
                                                    } else {
                                                        $content .= "<br>";
                                                    }
                                                    $content .= $row["user"];
                                                }

                                                echo "<td data-wocheid='$wochencounter' data-id='$stundencounter' class=''>$content</td>";
                                            }
                                        }


                                        echo "</tr>";
                                    } else {
                                        echo "<tr><td colspan='7'><span class='text-muted'>Pause</span></td></tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        if ($nummer + 1 == 1) {
                            echo "<button class='btn btn-primary' id='copyBtn'>Für alle anderen Wochen übernehmen</button>";
                        }
                        ?>
                    </div>
                    <br><br><br>
                <?php } ?>
                <button id="saveBtn" class="btn btn-success btn-block">Speichern</button>
            </div>



        </div>
    </div>

    <?php if (!isset($_GET[ADMINKEY])) { ?>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

        <script>
            $(window).ready(function() {

                $('#nameInput').val(localStorage.getItem("name"))
                if ($("#nameInput").val() == 0 || $("#nameInput").val() == "" || $("#nameInput").val() == null) {
                    $("#stundenplaene").hide();
                } else {
                    $("#stundenplaene").show();
                }

                $('#nameInput').on('input', function() {
                    localStorage.setItem("name", $(this).val())
                    if ($(this).val() == 0 || $(this).val() == "" || $(this).val() == null || $(this).val().length < 4) {
                        $("#stundenplaene").hide();
                    } else {
                        $("#stundenplaene").show();
                    }
                });

                $(".choose").click(function() {
                    $(this).toggleClass("notime");
                    $(this).toggleClass("yestime");
                });
                $("#copyBtn").click(function() {
                    //console.log("btn clicked");
                    $(this).parent().find(".choose").each(function() {
                        //console.log($(this));
                        orig = $(this);

                        $(document).find("[data-id='" + $(this).data("id") + "']").each(function() {

                            if (orig.hasClass("yestime")) {
                                $(this).addClass("yestime");
                            } else {
                                $(this).removeClass("yestime");
                            }
                            if (orig.hasClass("notime")) {
                                $(this).addClass("notime");
                            } else {
                                $(this).removeClass("notime");
                            }
                        });
                    });
                });
                $("#saveBtn").click(function() {
                    var fields = [];
                    $(this).parent().find(".choose").each(function() {
                        fields.push({
                            woche: $(this).data("wocheid"),
                            stunde: $(this).data("id"),
                            zeit: !$(this).hasClass("notime")
                        });
                    });
                    var data = {
                        action: "save",
                        user: $("#nameInput").val(),
                        data: fields
                    }
                    $.ajax({
                        url: "index.php",
                        data: data,
                        method: "POST"
                    }).done(function(data) {
                        if (data != "") {
                            console.log(data);
                            alert("Fehler beim Speichern, bitte Strg+I drücken, auf den Reiter Console gehen und dann den Inhalt an Hannes schicken!")
                        } else {
                            alert("Gespeichert!");
                        }

                    });
                });

            });
        </script>
    <?php } ?>
</body>

</html>