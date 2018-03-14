<?php
    require_once('dbconnection.php');
    require_once('rappor.php');
    $fiestadb   = Db::getInstance();
    $time       = 0;
    if(isset($_GET['time'])) $time = $_GET['time'];
    $sensors    = 0;
    $sensors_n  = 0;
    $from       = new DateTime("{$_GET['from']} $time:00:00");
    $to         = new DateTime("{$_GET['from']} $time:59:00");
    $real_min   = null;
    $real_max   = null;
    $real_total = 0;
    $noisy_total= 0;
    $real_stats = array();
    $noisy_stats= array();
    $sensor_val = array();
    $permanet_random = array();
    $decimals   = 0;
    $step       = 1;
    $query      = "SELECT  sensorID, avg(value) as value from AirTemperature where datetime >='{$from->format("Y-m-d H:i:00")}' AND datetime <= '{$to->format("Y-m-d H:i:00")}' group by sensorID";
    $records    = $fiestadb->query($query);
    while ($result	= $records->fetch_assoc()){
        $value = number_format($result['value'],$decimals);
        array_push($sensor_val,$value);
        $sensors++;
        $real_total += $value;
        if (isset ($real_stats[$value] ))
            $real_stats[$value] +=1;
        else
            $real_stats[$value] = 1;
        if (!isset($real_min) || floatval($value) < $real_min)
            $real_min = floatval($value);
        if (!isset($real_max) || floatval($value) > $real_max)
            $real_max = floatval($value);
         
    }
    $min = $real_min;
    $max = $real_max;
    if(isset($_GET['min'])) $min = $_GET['min'];
    if(isset($_GET['max'])) $max = $_GET['max'];
    for ($x = 0; $x < $sensors; $x++)
    {
        $rappor_bi = array();
        for ($y=$min; $y<=$max; $y+=$step)
        {
            $true_answer = 0;
            if ($sensor_val[$x] == $y) 
                $true_answer = 1;
            $random_value = permanent_randomized_response($true_answer);
            $rappor_bi[strval($y)]= $random_value;
        } 
        array_push($permanet_random, $rappor_bi);
    }
    for ($x = 0; $x < $sensors; $x++)
    {
        for ($y=$min; $y<=$max; $y+=$step)
        {
            $key = strval($y);
            if (isset ($noisy_stats[$key] ))
                $noisy_stats[$key] += $permanet_random[$x][strval($y)];
            else
                $noisy_stats[$key] =  $permanet_random[$x][strval($y)];
        }
    }

?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(drawCharts);
      function drawCharts() {
        drawRealStats();
      }
      function drawRealStats() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Temperature');
        data.addColumn('number', 'Without noise');
        data.addColumn('number', 'With noise');
        data.addRows([
          <?php
          for ($x=$min; $x<=$max; $x+=$step)
          {
              $real_measurment = 0;
              $esti_measurment = 0;
              $positive_values = 0;
              $key = strval($x);
              if (isset($real_stats[$key])) $real_measurment = $real_stats[$key];
              if (isset($noisy_stats[$key]))
              {
                 $positive_values = $noisy_stats[$key];
              }
              $normalized_ratio = max(0, ($positive_values/floatval($sensors)) - 0.25);
              $esti_measurment = 2* $normalized_ratio * $sensors;
              $sensors_n += $esti_measurment;
              $noisy_total += $esti_measurment * $x;
              echo "['$x', $real_measurment,$esti_measurment],\n";
          }
          ?>
          ]);
          
          
        

        var options = {
        title: 'Number of measurments per temperature degree with and without noise addition',
        bar: {groupWidth: "95%"},
      };

        var chart = new google.visualization.ColumnChart(document.getElementById('real_measurments'));
        chart.draw(data, options);
      }
    </script>
</head>
<body>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">List of sensors</a></li>
            <li class="breadcrumb-item active"><a href="day.php?from=<?= $from->format("Y-m-d")?>">Day statistics</a></li>
            <li class="breadcrumb-item active" aria-current="page">Differential privacy</li>
        </ol>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="alert alert-dark" role="alert">
                <p>
                    Temperature measurements <br/>
                    <b>Date: </b><?=$_GET['from']?><br/>
                    <b>Time: </b><?="$time:00 - $time:59"?><br/>
                    <b>Min: </b><?=$real_min?> <b>Max: </b><?=$real_max?> (Real Measurements)<br/>
                </p>
                </div>
                <form class="form-inline" method="GET">
                    <input type="hidden" name="from" value="<?=$_GET['from']?>">
                    <input type="hidden" name="time" value="<?=$_GET['time']?>">
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="min" class="col-sm-2 col-form-label">Min</label>
                        <input type="text" class="form-control" id="min" name="min" mplaceholder="min" value="<?=$min?>">
                    </div>
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="max" class="col-sm-2 col-form-label">Max</label>
                        <input type="text" class="form-control" id="max" name="max" mplaceholder="max" value="<?=$max?>">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Update limits</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div id="real_measurments" style="width:100%; height:400px"></div>
            </div>
            <div class="col-4">
                <b>Real average:</b> <?php echo number_format($real_total/$sensors,2); ?><br/>
                <b>Average after noise:</b> <?php echo number_format($noisy_total/$sensors_n,2); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="alert alert-info" role="alert">
                Sensors' reports (X = real value, Green = reported)
           </div>
            <table class="table table-bordered  table-sm">
                <thead>
                <tr>
                    <th scope="col">Sensor #</th>
                    <?php for ($x =$min; $x<=$max; $x+=$step) {?>
                    <th scope="col" class="text-center"><?=$x?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                for ($y =0; $y<$sensors; $y++)
                {
                    $real_answer   = $sensor_val[$y];
                    echo "<tr><td>".($y+1)."</td>";
                    for ($x =$min; $x<=$max; $x+=$step)
                    {
                        $random_answer = $permanet_random[$y][strval($x)];
                        
                        echo '<td class="text-center';
                        if ($random_answer == 1) echo ' bg-success';
                        echo '">';
                        if ( $real_answer == $x) echo 'X';
                        echo "</td>";
                    }
                    echo "</tr>\n";
                }
                ?>
                </tbody>
            </table>
                
            </div>
        </div>
      </div>
        
    
</body>
</html>
