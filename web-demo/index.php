<?php

    require_once('dbconnection.php');
    $fiestadb = Db::getInstance();
    $query = "SELECT * from TempSmartSantander;";
    $records = $fiestadb->query($query);
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
      #map {
        width: 1024px;
        height: 700px;
        background-color: grey;
      }
    </style>
  </head>
  <body>
    <h3>Smart Santander</h3>
    <div id="map"></div>
    <br/>
    <a href="day.php?from=2017-12-2" class="btn btn-primary mb-2">Day statistics</a>
     <script>
      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 15,
          center: {lat: 43.46293, lng: -3.80901}
        });
        <?php
        while ($result	= $records->fetch_assoc()){?>
        var marker = new google.maps.Marker({
          position: {lat: <?php echo $result['latitude']?>, lng: <?php echo $result['longitude']?>},
          map: map,
          url:"http://mm.aueb.gr/fiesta/viewsensor.php?from=2017-12-1&to=2017-12-1&id[]=<?=$result['id']?>",
          label:"<?=$result['id']?>"
        });
        google.maps.event.addListener(marker, 'click', function() {
            window.location.href = this.url;
        });
        <?php
        }
        ?>
        
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCT2v_AeeGX1u7ZslwhLfyP_5Chji06EtM&callback=initMap">
    </script>
 
  </body>
</html>

