<?php

/**
 * Created by Mit Ggauz
 */
class GgauzExchange
{
    const DB_VERSION = '1.3';
    const TABLE_NAME = 'mge_exchange';
    const BNM_XML_URL = 'http://www.bnm.md/en/official_exchange_rates?get_xml=1&date=';

    /**
     * Constructor
     */
    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * Get currencies that would be shown
     * @url http://index.minfin.com.ua/code/
     * @return array
     */
    public function getCurrenciesToShow()
    {
        return array(840, 978, 826, 756, 980);
    }

    /**
     * Get default chart currency
     * @return int
     */
    public function getDefaultChartCurrency()
    {
        return 978;
    }

    /**
     * Get default value of MDL in calculator
     * @return int
     */
    public function getDefaultValue()
    {
        return 100;
    }

    /**
     * Get number of days for chart drawing
     * @return int
     */
    public function getDaysForChart()
    {
        return 7;
    }

    /**
     * Get Plugin's table name with prefix
     * @return string
     */
    public function getTableName()
    {
        return $this->db->prefix . self::TABLE_NAME;
    }

    /**
     * Get Plugin's table version
     * @return string
     */
    public function getDbVersion()
    {
        return self::DB_VERSION;
    }

    /**
     * Convert DateTime to the right format
     * @param $date DateTime
     * @return mixed
     */
    public function getFormattedDate($date)
    {
        return $date->format('d.m.Y');
    }

    /**
     * Get today's formatted date
     * @return mixed
     */
    public function getCurrentDate()
    {
        $date = new DateTime();
        return $this->getFormattedDate($date);
    }

    /**
     * Get yesterday's formatted date
     * @return mixed
     */
    public function getYesterdayDate()
    {
        $date = new DateTime();
        $date->modify('-1 day');
        return $this->getFormattedDate($date);
    }

    /**
     * Create table SQL query
     * @param $tableName string
     * @return string
     */
    public function getTableSqlSchema($tableName)
    {
        $charset = $this->db->get_charset_collate();

        return "CREATE TABLE $tableName (
            id int(10) NOT NULL AUTO_INCREMENT,
            code INTEGER(6),
            symb VARCHAR(6),
            rate VARCHAR(12),
            diff VARCHAR(12),
            date VARCHAR(12),
            created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset ;";
    }

    /**
     * Drop table SQL query
     * @return string
     */
    public function getDropTableSqlSchema()
    {
        $tableName = $this->getTableName();

        return "DROP TABLE IF EXISTS $tableName ;";
    }

    /**
     * Get xml by date and parse data
     * @param $date string
     * @return array
     */
    public function getXmlDataByDate($date)
    {
        $result = array();
        $url = self::BNM_XML_URL . $date;
        $get_xml_today = file_get_contents($url, 0);
        $xml_today = new SimplexmlElement($get_xml_today);
        $xml_date = (string) $xml_today->attributes()->{'Date'};

        foreach ($xml_today->Valute as $ind => $item) {
            $codeNumb = (string) trim($item->NumCode);
            $charCode = (string) trim($item->CharCode);
            $rate = (float) $item->Value / (float) $item->Nominal;

            $result[$codeNumb]['code'] = $codeNumb;
            $result[$codeNumb]['symb'] = $charCode;
            $result[$codeNumb]['rate'] = $rate;
            $result[$codeNumb]['date'] = $xml_date;
        }

        return $result;
    }

    /**
     * Get last record date
     * @return mixed
     */
    public function getLastUpdatedDate()
    {
        $name = $this->getTableName();
        $lastRecordDate = $this->db->get_var("SELECT date FROM $name ORDER BY created DESC LIMIT 1");

        return ($lastRecordDate != null) ? $lastRecordDate : $this->getYesterdayDate();
    }

    /**
     * Set parsed data to the DB
     * @param $data
     * @return false|int
     */
    public function setXmlData($data)
    {
        return $this->db->insert($this->getTableName(), $data);
    }

    /**
     * Get last set of data
     * @return array
     */
    public function getLastDataSet()
    {
        $name = $this->getTableName();
        $currencies = $this->getCurrenciesToShow();
        $list = implode(',', $currencies);
        $count = count($currencies);

        $sql = "SELECT * FROM $name WHERE code IN ( $list ) ORDER BY created DESC LIMIT $count ";
        $result = $this->db->get_results($sql, ARRAY_A);

        return $result ? $result : array();
    }

    /**
     * Get data set for chart drawing
     * @param int $currency
     * @return mixed|string|void
     */
    public function getDataSetForChart($currency = null)
    {
        $currency = !$currency ? $this->getDefaultChartCurrency() : (int) $currency;
        $name = $this->getTableName();
        $limit = $this->getDaysForChart();

        $sql = "SELECT rate, date FROM $name WHERE code = $currency ORDER BY created DESC LIMIT $limit ";
        $result = $this->db->get_results($sql, ARRAY_A);

        return json_encode($result);
    }

    public function getClassName()
    {
        return get_class($this);
    }
}
