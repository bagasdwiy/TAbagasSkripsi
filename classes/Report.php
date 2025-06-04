<?php
abstract class Report {
    protected $db;
    protected $startDate;
    protected $endDate;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function setDateRange($startDate, $endDate) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        return $this;
    }
    
    abstract public function getData();
    abstract public function exportPDF();
    abstract public function exportExcel();
}
