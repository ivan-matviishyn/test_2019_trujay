<?php
const FILE_NAME = 'MOCK_DATA_TEST_TI.csv';
function readFromFile($fileName)
{
    $lines = explode( "\n", file_get_contents($fileName) );
    $headers = str_getcsv(array_shift($lines));
    foreach ( $lines as $line ) {
        $row = array();
        foreach ( str_getcsv( $line ) as $key => $field )
            $row[ $headers[ $key ] ] = $field;
        //$row = array_filter( $row );
        $data[] = $row;
    }
    return $data;
}

function getHeaders($fileName)
{
    $lines = explode( "\n", file_get_contents($fileName) );
    return str_getcsv(array_shift($lines));
}

function setDataFromFile($fileResultName,$headers, $setData)
{
    $handle = fopen($fileResultName.".csv", "w+");
    fputcsv($handle, $headers);
    foreach ($setData as $value) {
        fputcsv($handle, $value);
    }
}
$data = readFromFile(FILE_NAME);
foreach ($data as $key => &$value) {
    $value['phone'] = preg_replace('/[^0-9]/','', $value['phone']);
    if (false === empty($value['birthday'])) {
        $valueData = DateTime::createFromFormat('Y-m-d', $value['birthday']);
        $value['birthday'] = $valueData->format('m-d-Y');
    }
}
setDataFromFile('endFile',getHeaders(FILE_NAME),$data);