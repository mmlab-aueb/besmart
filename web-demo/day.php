<?php
    require_once('dbconnection.php');
    $fiestadb = Db::getInstance();
    $table    =  array();
    $time     = 0;
    $sensors  = 70;
    $from     = new DateTime("{$_GET['from']} $time:00:00");
    $to       = new DateTime("{$_GET['from']} $time:59:00");
    for ($x =$time; $x<24; $x++)
    {
        $row = array();
        $query = "SELECT  sensorID, avg(value) as value from AirTemperature where datetime >='{$from->format("Y-m-d H:i:00")}' AND datetime <= '{$to->format("Y-m-d H:i:00")}' group by sensorID";
        $records = $fiestadb->query($query);
        while ($result	= $records->fetch_assoc()){
             array_push($row,$result['value']);
        }
        $from->modify("+1 hours");
        $to->modify("+1 hours");
        array_push($table,$row);
    }
    $from = new DateTime("{$_GET['from']} $time:00:00");
    $to   = new DateTime("{$_GET['from']} $time:59:00");
?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<?php if (isset($_GET['cvs']))
{
    for ($y =0; $y<$sensors; $y++)
        {
            for ($x = 0; $x<24; $x++)
            {
                echo number_format($table[$x][$y],1).",";
                
            }
            echo number_format($table[21][$y],1)."<br/>";
        }
}
else
{?>
<body>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">List of sensors</a></li>
            <li class="breadcrumb-item active" aria-current="page">Day statistics</li>
        </ol>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="alert alert-dark" role="alert">
                <p>
                    Average temperature per sensor per hour<br/>
                    <b>Date: </b><?=$_GET['from']?><br/>
                </p>
                </div>
                <form class="form-inline" method="GET">
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="from" class="col-sm-2 col-form-label">From</label>
                        <input type="text" class="form-control" id="from" name="from" mplaceholder="from" value="<?=$_GET['from']?>">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Update Date</button>
                </form>
            </div>
        </div>
        <div class="row">
           <div class="col">
           <div class="alert alert-info" role="alert">
                Select a time to apply differential privacy
           </div>
            <table class="table  table-sm">
                <thead>
                <tr>
                    <th scope="col">Sensor #</th>
                    <?php for ($x =$time; $x<24; $x++) {?>
                    <th scope="col"><?php echo "<a href=\"privacy.php?from={$from->format("Y-m-d")}&time=$x\">".$x."</a>"; ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                for ($y =0; $y<$sensors; $y++)
                {
                    echo "<tr><td>".($y+1)."</td>";
                    for ($x =0; $x<24; $x++)
                    {
                        echo "<td>".number_format($table[$x][$y],1)."</td>";
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
<?php } ?>
</html>
