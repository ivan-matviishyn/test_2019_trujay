<?php

class Pipedrive
{
    protected $companyDomain;
    protected $apiToken;
    public const PIPEDRIVE_API_URL = '.pipedrive.com/v1/';
    public function __construct($companyDomain = null,$apiToken = null)
    {
        $this->setCompanyDomain($companyDomain);
        $this->setApiToken($apiToken);
    }
    private function setCompanyDomain($companyDomain)
    {
        $this->companyDomain = $companyDomain;
    }
    private function getCompanyDomain()
    {
        return $this->companyDomain;
    }
    private function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }
    private function getApiToken()
    {
        return $this->apiToken;
    }
    public function request(string $entity, string $typeRequest, array $parametr = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://' . $this->getCompanyDomain() . '.pipedrive.com/v1/' . $entity . '?api_token=' . $this->getApiToken() . $this->getParameter($parametr),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $typeRequest,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true);
    }
    private function getParameter(array $parametr = []) : string
    {
        $result = '';
        if ($parametr !== []) {
           foreach ($parametr as $key => $value) {
               $result = $result . '&' . $key .'=' . $value;
           }
        }

        return $result;
    }
    public function getAllRecordsFromEntity(string  $entity) : array
    {
        $records = [];
        $step = 10;
        $start = 0;
        do {
            $response = $this->request($entity,'GET',['limit' => $step, 'start' => $start]);
            if (!empty($response['data'])) {
                foreach ($response['data'] as $value){
                    $records [] = $value;
                }
            }
            $start += $step;

        } while ($response['additional_data']['pagination']['more_items_in_collection'] == true);
        return $records;
    }
    public function getAllFieldsFromEntity(string  $entity) : array
    {
        $fields = [];
        $response = $this->request($entity. 'Fields','GET');
        foreach ($response['data'] as $key => $value){
            $fields[$value['key']] = $value['name'];
        }
        return $fields;
    }
    public function getView(string  $entity, $fields, $records, $notes = null, $activity = null)
    {
        if (!empty($activity)) $fields['activity'] = 'Activity            ';
        if (!empty($notes)) $fields['note'] = 'Notes   ';
        echo '<table id="customers">
		        <thead>
                <tr>
                    <th colspan="'. count($fields) .'">' . $entity . '</th>
                </tr><tr>';
        foreach ($fields as $key => $value) {
            echo  '<th>' . $value . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($records as $record) {
            if(!empty($notes)) $record['note'] = $this->getRecordsForOne('note',$entity,$record['id'],$notes);
            if(!empty($activity)) $record['activity'] = $this->getRecordsForOne('activity',$entity,$record['id'],$activity);
            echo '<tr>';
            foreach ($fields as $key => $value) {
                if(is_array($record[$key])) {
                    switch($key) {
                        case "owner_id": echo '<td>' . $record[$key]['name'] . '</td>';
                            break;
                        case "creator_user_id": echo '<td>' . $record[$key]['name'] . '</td>';
                            break;
                        case "user_id": echo '<td>' . $record[$key]['name'] . '</td>';
                            break;
                        case "person_id": echo '<td>' . $record[$key]['name'] . '</td>';
                            break;
                        case "org_id": echo '<td>' . $record[$key]['name'] . '</td>';
                            break;
                        case "phone": echo '<td>' . $record[$key][0]['value'] . '</td>';
                            break;
                        case "email": echo '<td>' . $record[$key][0]['value'] . '</td>';
                            break;
                    }
                } else {
                    echo '<td>' . $record[$key] . '</td>';
                }
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    public function getRecordsForOne(string $fromEntity,string $toEntity, $toId, array $from)
    {
        $fromEntityCount = 1;
        $list = [];
        $records = [];

        foreach ($from as $value) {
            if ($toEntity == 'Persons' && $value['person_id'] == $toId) {
                $records[] = $value;
            }
            if ($toEntity == 'Organizations' && $value['org_id'] == $toId) {
                $records[] = $value;
            }
            if ($toEntity == 'Deals' && $value['deal_id'] == $toId) {
                $records[] = $value;
            }
        }
        if ($records !== []){
            foreach ($records as $record){
                if($fromEntity == 'activity') $list[] = $fromEntityCount . '. ' . $record['subject'] . '<br>' . $record['note'];
                if($fromEntity == 'note') $list[] = $fromEntityCount . '. ' . $record['content'];
                $fromEntityCount++;
            }
        }
        return substr(implode('<br>',$list),0,200);
    }
    public function getAllFieldNotNull(array $records,array $fields) : array
    {
        $fieldsNoNull = [];
        foreach ($records as $record) {
            foreach ($fields as $key => $value) {
                if (!empty($record[$key])) {
                    $fieldsNoNull[$key] = $value;
                }
            }
        }
        return $fieldsNoNull;
    }
}