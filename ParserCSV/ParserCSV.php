<?php
abstract class ParserCSV {

    protected $separator;
    protected $encoding;
    protected $path;
    protected $dirCache;
    protected $pathWithoutDir;
    protected $positionColumns;

    /**
     * ParserCSV constructor.
     * @param $path
     * @param $separator
     * @param $encoding
     * @param $positionColumns
     * @param $pathWithoutDir
     */
    public function __construct($path, $separator, $encoding, $positionColumns, $pathWithoutDir) {
        $this->separator = $separator;
        $this->path = $path;
        $this->encoding = $encoding;
        $this->pathWithoutDir = $pathWithoutDir;
        $this->positionColumns = $positionColumns;
        $this->dirCache = $this->createDir(__DIR__ . '/../../../../ajax/modules/settings/cacheImport/');
    }

    /**
     * @return string
     */
    protected function getHTMLTable(){
        $sTextForServices = '';
        if ($this->positionColumns == 'notStatic') {
            $sTextForServices = '
        <div>
            <p class="text-head">Сопоставьте данные в таблице</p>
        </div>';
        }
        $aSeparatorsAndEncoding = $this->getSeparatorsAndEncoding();

        $sHTML = '<table class="renovatio_table"><thead><td style="display: flex; flex-direction: row; justify-content: space-between;">' . $sTextForServices . ' 
        <div class="select-encoding">
            <div>
            <input type="hidden" value="' . $this->pathWithoutDir . '" class="path-file-import"/>
                <p class="text-head" style="font-size: 12px;"> Если данные неправильно разделены или вместо текста вы видите что-то непонятное, то поменяйте кодировку или разделитель</p>
            </div>
            <div style="margin-right: 2px;">
                <select class="encoding">' .
            $aSeparatorsAndEncoding[0]
            . '</select>
            </div>
            <div>
                <select class="separator-import">' .
            $aSeparatorsAndEncoding[1]
            . '</select>
            </div>
        </div>
    </td></thead></table> 
    <table style="text-align: center;" id="table-import"  class="renovatio_table">
        <thead id="select-block">
        </thead>
        <tbody id="table-body-import">';
        return $sHTML;

    }

    abstract public function readFile();

    /**
     * @param $dir
     * @return mixed
     */
    private function createDir($dir) {
        if (!file_exists(__DIR__ .'/../../../../ajax/modules/settings/cacheImport/')) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    abstract function getOptionsOnHead($num, $sForm);

    /**
     * @param $aOption
     * @param $sSearchElement
     * @return string
     */
    private function getListOption($aOption, $sSearchElement) {
        $sListOption = '';
        foreach ($aOption as $key => $item) {
            if (stristr($item, $sSearchElement) !== false) {
                $nowItem = $aOption[$key];
                unset($aOption[$key]);
                array_unshift($aOption, $nowItem);
                $sListOption = implode(',', $aOption);
            }
        }
        return $sListOption;
    }

    /**
     * @return array
     */
    private function getSeparatorsAndEncoding() {
        $aSeparatorsHTML = [
            '<option>;</option>',
            '<option>,</option>',
        ];
        $aEncodingHTML = [
            '<option>utf-8</option>',
            '<option>windows-1251</option>',
        ];

        return [$sListEncoding = $this->getListOption($aEncodingHTML, $this->encoding), $sListSeparators = $this->getListOption($aSeparatorsHTML, $this->separator)];
    }
}