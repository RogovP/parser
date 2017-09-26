<?php
class ParserFactory
{
    protected $parserFrom;

    protected $separator;
    protected $encoding;
    protected $path;
    protected $pathWithoutDir;
    protected $positionColumns;

    public function __construct($parserFrom, $path, $separator, $encoding, $positionColumns, $pathWithoutDir) {
        $this->parserFrom = $parserFrom;
        $this->separator = $separator;
        $this->path = $path;
        $this->encoding = $encoding;
        $this->pathWithoutDir = $pathWithoutDir;
        $this->positionColumns = $positionColumns;
    }

    public function getParser(){
        switch ($this->parserFrom) {
            case 'file/import':
                return new PatientsParser($this->path, $this->separator, $this->encoding, $this->positionColumns, $this->pathWithoutDir);
            case 'settings/services':
                return new ServicesParser($this->path, $this->separator, $this->encoding, $this->positionColumns, $this->pathWithoutDir);
        }
    }
}