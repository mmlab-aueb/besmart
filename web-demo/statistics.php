<?php
    require_once('dbconnection.php');
    $fiestadb = Db::getInstance();
    $query    = "select distinct sensorID from AirTemperature";
    $records  = $fiestadb->query($query);
    $sensors  = array();
    $counter  =1;
    while ($result	= $records->fetch_assoc()){
        $query2   = "select * from AirTemperature where sensorID='{$result['sensorID']}' limit 1";
        $records2 = $fiestadb->query($query2);
        $result2  = $records2->fetch_assoc();
        $sensors[$counter]=['sensorID'=>$result2['sensorID'], 'latitude'=>$result2['latitude'],'longitude'=>$result2['longitude'] ];
        $counter++;
    }
     
    foreach ($sensors as $key=>$sensor){
        echo "Insert into TempSmartSantander (sensorID,latitude,longitude) values ('{$sensor['sensorID']}','{$sensor['latitude']}','{$sensor['longitude']}');\n ";
    }
?>
