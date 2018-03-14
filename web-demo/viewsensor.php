<?php
    require_once('dbconnection.php');
    $fiestadb = Db::getInstance();
    $where    =  "";
    foreach ($_GET['id'] as $sensorID)
    {
        $where .=" sensorID = '$sensorID' OR";
    }
    $where = rtrim( $where, 'OR' );
    $query = "SELECT * from AirTemperature where ($where) and datetime >='{$_GET['from']} 00:00:00' AND datetime <= '{$_GET['to']} 23:59:59' order by datetime";
    $table =  array();
    $records = $fiestadb->query($query);
    while ($result	= $records->fetch_assoc()){
        $row = array();
        $date  = $result['datetime'];
        $date  = strtotime($date);
        array_push($row,"new Date($date*1000)");
        foreach ($_GET['id'] as $sensorID)
        {
            if ($result['sensorID'] == $sensorID)
                array_push($row,$result['value']);
            else
                array_push($row,'null');
        }
        array_push($table,$row);
    }
        
?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.load('current', {'packages':['table']});

        google.charts.setOnLoadCallback(drawCharts);
        function drawCharts()
        {
           drawChart();
        }
        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Date');
            <?php
            foreach ($_GET['id'] as $sensorID)
            {?>
            data.addColumn('number', 'Sensor #<?=$sensorID?>');
            <?php } ?>
            data.addRows([
              <?php
                foreach ($table as $row)
                {
                    echo "[".implode(",", $row)."],\n";
                }
              ?>
            ]);

            var options = {
              explorer: {axis: 'both'},
              interpolateNulls: true
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
          }
    </script>
</head>
<body>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">List of sensors</a></li>
            <li class="breadcrumb-item active" aria-current="page">Measurements</li>
        </ol>
    </nav>


    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="alert alert-dark" role="alert">
                <p>
                    <b>Sensor #</b><?=$_GET['id'][0]?><br/>
                    <b>From: </b><?=$_GET['from']?> 00:00:00<br/>
                    <b>To: </b><?=$_GET['to']?> 23:59:59<br/>
                </p>
                </div>
                <form class="form-inline" method="GET">
                    <input type="hidden" name="id[]" value="<?=$_GET['id'][0]?>">
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="from" class="col-sm-2 col-form-label">From</label>
                        <input type="text" class="form-control" id="from" name="from" mplaceholder="from" value="<?=$_GET['from']?>">
                    </div>
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="to" class="col-sm-2 col-form-label">To</label>
                        <input type="text" class="form-control" id="to" name="to" placeholder="to" value="<?=$_GET['to']?>">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Update Intervals</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div  id="curve_chart" style="width:100%; height:700px"></div>
            </div>
            <div class="col-4">
            <form method="GET">
            <input type="hidden" name="from" value="<?=$_GET['from']?>">
            <input type="hidden" name="to" value="<?=$_GET['to']?>">
            <input type="hidden" name="id[]" value="<?=$_GET['id'][0]?>">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th scope="col">Sensor #</th>
                    <th scope="col">Distance (m)</th>
                    
                </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT * from TempSmartSantander where id='{$_GET['id'][0]}'";
                $records = $fiestadb->query($query);
                $result	= $records->fetch_assoc();
                $latitude = $result['latitude'];
                $longitude = $result['longitude'];
                $query = "SELECT id, ( 6371000 * acos(cos(radians($latitude)) * cos(radians(latitude))*cos(radians(longitude) - 
                radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance FROM TempSmartSantander Having distance >1 ORDER BY distance asc limit 0,15";
                $records = $fiestadb->query($query);
                while ($result	= $records->fetch_assoc()){
                    echo "<tr><td><input type=\"checkbox\" name=\"id[]\" value=\"{$result['id']}\"";
                    if (in_array($result['id'], $_GET['id'])) echo " checked ";
                    echo "/></td><td>".$result['id']."</td><td>".round($result['distance'],2)."</td></tr>";
                }
                ?>
                <td></td><td></td><td><button type="submit" class="btn btn-primary mb-2">Plot</button></td>
                </tbody>
            </table>
            </form>
            </div>
        </div>
    </div>
</body>
</html>

