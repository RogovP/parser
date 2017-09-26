<?php
require_once 'ParserCSV.php';
require_once 'ExceptionParsing/PatientException.php';
class PatientsParser extends ParserCSV
{
    /**
     * PatientsParser constructor.
     * @param $path
     * @param $separator
     * @param $encoding
     * @param $positionColumns
     * @param $pathWithoutDir
     */
    public function __construct($path, $separator, $encoding, $positionColumns, $pathWithoutDir) {
        parent::__construct($path, $separator, $encoding, $positionColumns, $pathWithoutDir);
    }

    /**
     * @return string
     */
    public function readFile(){
        $sHTML = $this->getHTMLTable();
        $sHead = $sTextForServices = '';
        $aServicesWithCategory = $aServicesCategory = $aPatient = $aOptions = [];
        if (($handle = fopen($this->path, "r")) !== FALSE) {
            $iCololumn = [];
            while (($data = fgetcsv($handle, 500, $this->separator)) !== FALSE) {
                $aOneService = [];
                $data = array_diff($data, ['']);
                $num = 13;
                $sHTML .= '<tr>';
                for ($c = 0; $c < $num; $c++) {
                    $sDataServices = trim($data[$c]);
                    $sHTML .= '<td>' . $sDataServices . '</td>';
                    $aOneService[] = $sDataServices;
                    $iCololumn[$c] = $c;
                }
                $sHTML .= '</tr>';
                array_push($aPatient, $aOneService);
            }
            $sHTML = $this->getColorTablePatient($aPatient, $sHTML);
            $aAllOptions = $this->getOptionsOnHead($num, $this->positionColumns);
            for ($i = 0; $i < $num; $i++) {
                array_push($aOptions, $aAllOptions[$i]);
            }
            foreach ($aOptions as $value) {
                $sHead .= '<th>' . $value . '</th>';
            }
            $sBodyTable = explode('id="select-block">', $sHTML);
            $sHTML = $sBodyTable[0] . 'id="select-block"><tr>' . $sHead . '</tr>' . $sBodyTable[1];
            file_put_contents($this->dirCache . 'patients.json', json_encode(serialize($aPatient), JSON_UNESCAPED_UNICODE));
            fclose($handle);
            $sHTML .= '</tr></tbody> </table>';
            return $sHTML;
        }
    }

    /**
     * @param $aPatients
     * @param $sHTML
     * @return string
     * @throws PatientException
     */
    private function getColorTablePatient($aPatients, $sHTML) {
        $aTrBodyTable = array_diff(explode('<tr>', explode('<tbody id="table-body-import">', $sHTML)[1]), ['']);
        $aPatientDb = unserialize(json_decode(file_get_contents(__DIR__ . '/../../../../ajax/modules/settings/cacheImport/patientFromDb.json')));
        $sAddHTML = $sDate = '';
        $aPatientsForUpdate =  $aNotUpdateKeys = [];
        foreach ($aTrBodyTable as $key => $value) {
            $valueTd = explode('<td>', $value);
            $sDateTable = $valueTd[4];
            if ((int) $sDateTable == '') {
                throw new PatientException('Дата рождения не найдена.<br> Обязательные поля: ФИО, дата рождения');
            }
            foreach ($aPatients as $datePatient) {
                if (strpos($sDateTable, $datePatient[3]) !== false) {
                    $sDate = $datePatient[3];
                    break;
                }
            }
            $sDate = date('Y-m-d', strtotime(trim($sDate)));
            foreach ($aPatientDb as $item) {
                if (strpos($value, trim($item[0])) !== false) {
                    if (strpos($value, trim($item[1])) !== false) {
                        if (strpos($value, trim($item[2])) !== false) {
                            if (strpos($sDate, trim($item[3])) !== false) {
                                $aNotUpdateKeys[] = $key;
                                $value = '<tr style="color:dimgrey;">' . $value;
                            }
                        }
                    }
                }
            }
            if (strpos($value, 'color:red') === false) {
                $value = '<tr style="color:blue;">' . $value;
                if (!in_array($key, $aNotUpdateKeys)){
                    $aStrForWrite = array_values(array_diff(explode('<td>', $aTrBodyTable[$key]), ['']));
                    $aStrForWrite[3] = date('Y-m-d', strtotime(trim(explode('</td>', $aStrForWrite[3])[0])));
                    $aOnlyInformation = [];
                    foreach ($aStrForWrite as $item) {
                        array_push($aOnlyInformation, explode('</td>', $item)[0]);
                    }
                    array_push($aPatientsForUpdate, $aOnlyInformation);
                }
            }
            $sAddHTML .= $value;
        }

        file_put_contents( $this->dirCache . 'patientsForUpdate.json', json_encode(serialize($aPatientsForUpdate), JSON_UNESCAPED_UNICODE));
        $sHTML = explode('<tbody id="table-body-import">', $sHTML)[0] . '<tbody id="table-body-import">' . $sAddHTML;
        return $sHTML;
    }

    /**
     * @param $num
     * @param $sForm
     * @return array
     */
    public function getOptionsOnHead($num, $sForm){
        return  [
                'Фамилия',
                'Имя',
                'Отчество',
                'Дата рождения',
                'Мобильный телефон',
                'Домашний телефон',
                'Email',
                'Город',
                'Улица',
                'Дом',
                'Корпус',
                'Квартира',
                'Комментарий',
            ];
    }
}