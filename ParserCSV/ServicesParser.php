<?php
require_once 'ParserCSV.php';
require_once 'ExceptionParsing/PatientException.php';
class ServicesParser extends ParserCSV
{
    /**
     * ServicesParser constructor.
     * @param $path
     * @param $separator
     * @param $encoding
     * @param $positionColumns
     * @param $pathWithoutDir
     */
    public function __construct($path, $separator, $encoding, $positionColumns, $pathWithoutDir)
    {
        parent::__construct($path, $separator, $encoding, $positionColumns, $pathWithoutDir);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function readFile(){
        $sHTML = parent::getHTMLTable();
        $sHead = $sTextForServices = '';
        $aServicesWithCategory = $aServicesCategory = $aPatient = $aOptions = [];
        if (($handle = fopen($this->path, "r")) !== FALSE) {
            $iCololumn = [];
            while (($data = fgetcsv($handle, 500, $this->separator, $this->separator)) !== FALSE) {
                $aOneService = [];
                $data = array_diff($data, ['']);
                $num = count($data);
                if ($num == 1 && !ctype_digit($data[0]) && stristr($data[0], '.') === false) {
                    for ($c = 0; $c < 1; $c++) {
                        $sDataCategory = trim($data[$c]);
                        /**
                         * Удаление постоянного символа при переводе из windows 1251
                         */
                        if (stristr($sDataCategory, 'п»ї') === false) {
                            $sHTML .= '<tr><th colspan="4">' . $sDataCategory . '</th></tr>';
                        } else {
                            $sDataCategory = explode('п»ї', $sDataCategory)[1];
                            $sHTML .= '<tr><th colspan="4">' . $sDataCategory . '</th></tr>';
                        }
                        $aServicesCategory[] = $sDataCategory;
                    }
                } else {
                    $sHTML .= '<tr>';
                    for ($c = 0; $c < $num; $c++) {
                        $sDataServices = trim($data[$c]);
                        $sHTML .= '<td>' . $sDataServices . '</td>';
                        $aOneService[] = $sDataServices;
                        $iCololumn[$c] = $c;
                    }
                    $sHTML .= '</tr>';
                    if (!$sDataCategory || $sDataCategory == '') {
                        throw new PatientException('Category not found in this file');
                    }
                    if (count($aOneService) != 4) {
                        array_pop($aOneService);
                    }
                    array_push($aOneService, $sDataCategory);
                    array_push($aServicesWithCategory, $aOneService);
                }
            }

            $aOptions = $this->getOptionsOnHead($num, $this->positionColumns);
            /**
             * Подсчет колонок в таблице
             */
            foreach ($iCololumn as $item) {
                if ($item == 0) {
                    $sListOption = implode(',', $aOptions);
                } else {
                    $nowItem = $aOptions[$item];
                    unset($aOptions[$item]);
                    array_unshift($aOptions, $nowItem);
                    $sListOption = implode(',', $aOptions);
                }

                $sHead .= '<td>
                <select id="col-'. $item .'">' . $sListOption . '</select></td>';

            }
            $sBodyTable = explode('id="select-block">', $sHTML);
            $sHTML = $sBodyTable[0] . 'id="select-block">' . $sHead . $sBodyTable[1];
        }
        fclose($handle);
        $sHTML .= '</tr></tbody> </table>';

        file_put_contents($this->dirCache . 'servicesCategory.json', json_encode(serialize($aServicesCategory), JSON_UNESCAPED_UNICODE));

        return $sHTML;
    }

    /**
     * @param $num
     * @param $sForm
     * @return array
     */
    public function getOptionsOnHead($num, $sForm){
        if ($num == 3) {
            $aOptions = [
                '<option>Код</option>',
                '<option>Название</option>',
                '<option>Стоимость</option>',
                '<option>Себестоимость</option>',
            ];
        } else {
            $aOptions = [
                '<option>Код</option>',
                '<option>Название</option>',
                '<option>Себестоимость</option>',
                '<option>Стоимость</option>',
            ];
        }
        return $aOptions;
    }
}