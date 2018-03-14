<?php
$from = new DateTime("2018-2-1 00:00:00");
$to   = new DateTime("2018-2-1 00:59:00");
//1 month = 720
for ($x =0; $x<720; $x++)
{ 
    echo "Requesting from".$from->format("YmdHi")." to ".$to->format("YmdHi")."\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://platform.fiesta-iot.eu/iot-registry/api/queries/execute/observations?from=".$from->format("YmdHi")."&to=".$to->format("YmdHi"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "
Prefix ssn: <http://purl.oclc.org/NET/ssnx/ssn#> 
Prefix iotlite: <http://purl.oclc.org/NET/UNIS/fiware/iot-lite#> 
Prefix dul: <http://www.loa.istc.cnr.it/ontologies/DUL.owl#> 
Prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
Prefix time: <http://www.w3.org/2006/time#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
Prefix m3-lite: <http://purl.org/iot/vocab/m3-lite#>
Prefix xsd: <http://www.w3.org/2001/XMLSchema#>
select ?sensorID ?datetime ?value ?latitude ?longitude ?qk ?unit
where { 
    ?o a ssn:Observation.
    ?o ssn:observedBy ?sensorID. 
    ?o ssn:observedProperty ?qkr.
    ?qkr rdf:type ?qk.
    #Values ?qk {m3-lite:AirTemperature m3-lite:TemperatureSoil m3-lite:Illuminance m3-lite:AtmosphericPressure m3-lite:RelativeHumidity m3-lite:WindSpeed m3-lite:SoundPressureLevel m3-lite:SoundPressureLevelAmbient m3-lite:Sound m3-lite:SolarRadiation m3-lite:ChemicalAgentAtmosphericConcentrationCO m3-lite:chemicalAgentAtmosphericConcentrationO3 }
    Values ?qk {m3-lite:ElectricField2100MHz}
    ?o ssn:observationSamplingTime ?t.
    ?o geo:location ?point. 
    ?point geo:lat ?latitude. 
    ?point geo:long ?longitude.
    ?t time:inXSDDateTime ?datetime.
    ?o ssn:observationResult ?or.
    ?or ssn:hasValue ?v. 
    ?v iotlite:hasUnit ?u.
    ?u rdf:type ?unit.
    ?v dul:hasDataValue ?value.
    #W3ID
    # FILTER ( 
    #    (xsd:double(?latitude) >= \"41.50000\"^^xsd:double) 
    # && (xsd:double(?latitude) <= \"43.50000\"^^xsd:double) 
    # && ( xsd:double(?longitude) >= \"-1.00000\"^^xsd:double)  
    # && ( xsd:double(?longitude) <= \"2.00000\"^^xsd:double)   
    #)
    #TERA
    #FILTER ( 
    #  (xsd:double(?latitude) >= \"40.00000\"^^xsd:double) 
    #&& (xsd:double(?latitude) <= \"42.00000\"^^xsd:double) 
    #&& ( xsd:double(?longitude) >= \"15.90000\"^^xsd:double)  
    #&& ( xsd:double(?longitude) <= \"17.70000\"^^xsd:double)
    #)
    FILTER (
        (xsd:double(?latitude) >= \"43.45000\"^^xsd:double)
        && (xsd:double(?latitude) <= \"43.48000\"^^xsd:double)
        && ( xsd:double(?longitude) >= \"-3.90000\"^^xsd:double)
        && ( xsd:double(?longitude) <= \"-3.70000\"^^xsd:double)
    )
    # FILTER ( 
    #   (xsd:double(?latitude) >= \"35.29000\"^^xsd:double) 
    #   && (xsd:double(?latitude) <= \"35.32000\"^^xsd:double) 
    #   && ( xsd:double(?longitude) >=\"24.90000\"^^xsd:double)  
    #   && ( xsd:double(?longitude) <= \"26.00000\"^^xsd:double)
    #)
    } 
order by ?datetime",
      CURLOPT_HTTPHEADER => array(
        "Accept: text/csv",
        "Content-Type: text/plain",
        "iPlanetDirectoryPro:FILTEREDOUT"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpcode == 200)
    {
        $file = __DIR__."/cvs/smart/electric/".$from->format("YmdHi")."-".$to->format("YmdHi").".csv";
        file_put_contents( $file, $response );
        $from->modify("+1 hours");
        $to->modify("+1 hours");
        sleep(5);
    }else
    {
        echo "Received $httpcode\n";
        $x--;
        sleep(5);
    }
    curl_close($curl);

}
