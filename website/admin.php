<?php
  require 'config.php';
?>

<html>

<head>
  <title>url shortener</title>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>
  <h2>Shorten URL</h2>
  <form id="shorten">
    <label>Name</label>
    <input type="text" id="name" name="name">
    <br>
    <label>URL</label>
    <input style="margin-top: 10px;" type="text" id="url" name="url" required>
    <br>
    <input style="margin-top: 10px;" type="submit" id="submit" name="submit" value="Submit">
  </form>
  <div id="output" style="height:36px;"></div>

  <br><hr><br>

  <h2>Edit URL</h2>
  <form id="edit">
    <label for="names">Choose a Link:</label>
    <select name="name" id="names" required>
    </select>
    <br>
    <label>New URL</label>
    <input style="margin-top: 10px;" type="text" id="url" name="url" required>
    <br>
    <input type="submit" value="Submit">
  </form>
  <div id="edit-output" style="height:36px;"></div>

  <br><hr><br>

  <h2>Shortened URLS</h2>
  <div id="shortened-list"></div>

  <script type='text/javascript'>
    function getShortenedURLS() {
      $.get(
          "<?php echo SHORT_URL; ?>api.php?type=list",
          function(data) {
              if (data["success"] == false) {
                  $("#shortened-list").html("<span style='color:red;font-weight:bold'>" + data["error"] + "</span>");
              } else if (data["success"]) {
                $("#shortened-list").html('<table id="urltable"><thead><tr><th>Name</th><th>Hits</th></thead></table>');
                data["urls"].forEach((element) => {
                  $("#urltable").append("<tr><th><a href='/" + element["name"] + "' target='_blank'>" + element["name"] + "</a><th>" + element["hits"] + "</th></tr>");
                });
              }
          }
      );
    }

    $(document).ready(function() {
      $.get(
          "<?php echo SHORT_URL; ?>api.php?type=list",
          function(data) {
              if (data["success"] == false) {
                $("#names").append("<option value=''>error loading</option>");
              } else if (data["success"]) {
                data["urls"].forEach((element) => {
                  $("#names").append("<option value='" + element["name"] + "'>" + element["name"]+ "</option>");
                });
              }
          }
      );
      getShortenedURLS();
    });

    $("#shorten").submit(function(event) {
        event.preventDefault();
        if ($("#name").val() != "") {
            var n = "&name=" + $("#name").val();
        } else {
            var n = "";
        }
        $.get(
            "<?php echo SHORT_URL; ?>api.php?type=new&url=" + $("#url").val() + n,
            function(data) {
                if (data["success"] == false) {
                    $("#output").html("<span style='color:red;font-weight:bold'>Error</span><br>" + data["error"]);
                } else if (data["success"]) {
                    $("#output").html("<span style='color:green;font-weight:bold'>Success</span><br>" + data["url"]);
                } else if (data["success"] == null) {
                    $("#output").html("<span style='color:orange;font-weight:bold'>Alredy Exists</span><br>" + data["url"]);
                }
                getShortenedURLS();
            }
        );
    });

    $("#edit").submit(function(event) {
      event.preventDefault();
      $.ajax({
             url: "<?php echo SHORT_URL; ?>api.php?type=edit",
             type: "post",
             data: $(this).serialize(),
             success: function (response) {
               if (response["success"] == false) {
                   $("#edit-output").html("<span style='color:red;font-weight:bold'>Error</span><br>" + response["error"]);
               } else if (response["success"]) {
                   $("#edit-output").html("<span style='color:green;font-weight:bold'>Success</span><br>New Target: " + response["new_target_url"]);
               } else if (response["success"] == null) {
                   $("#edit-output").html("<span style='color:orange;font-weight:bold'>Alredy Exists</span><br>" + response["url"]);
               }
               getShortenedURLS();
             },
             error: function(jqXHR, textStatus, errorThrown) {
               $("#edit-output").html("<span style='color:red;font-weight:bold'>Error</span><br>" + errorThrown);
             }
         });
    });
  </script>
  <style>
  table, th, td {
    border: 1px solid black;
  }
  table {
    border-collapse: collapse;
  }
  th {
    padding: 10px;
  }
</style>
</body>

</html>
