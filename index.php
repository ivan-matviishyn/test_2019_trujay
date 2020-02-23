<?php

include('Pipedrive.php');
echo'
<style>
	#customers {
		margin: auto;
    	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    	border-collapse: collapse;
	}
	#customers td, #customers th {
	    border: 1px solid #ddd;
	    padding: 8px;
	}
	#customers tr:nth-child(even){background-color: #f2f2f2;}
	#customers tr:hover {background-color: #ddd;}
	#customers th {
	    padding-top: 12px;
	    padding-bottom: 12px;
    	text-align: center ;
    	background-color: #4CAF50;
    	color: white;
	}
</style>';

const ENTITIES = [
    'organizations',
    'persons',
    'deals',
];

$connector = new Pipedrive('student15','ecb2fc657eff1da96017f789f6fa21f224d23d2b');

$notes = $connector->getAllRecordsFromEntity('notes');
$activity = $connector->getAllRecordsFromEntity('activities');

foreach (ENTITIES as $entity){
    $allRecords = $connector ->getAllRecordsFromEntity($entity);
    $entityFields = $connector ->getAllRecordsFromEntity(rtrim($entity,'s'));
    $entityFields = $connector ->getAllFieldsFromEntity(rtrim($entity,'s'));
    $entityFields = $connector ->getAllFieldNotNull($allRecords,$entityFields);
    $connector ->getView(ucfirst($entity),$entityFields, $allRecords, $notes, $activity);
}
