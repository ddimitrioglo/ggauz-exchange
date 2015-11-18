<?php

/**
 * Created by Mit Ggauz
 */
class GgauzExchange
{
    const TABLE_NAME = 'mge_exchange';

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
     * @param $tableName string
     * @return string
     */
    public function getTableSqlSchema($tableName)
    {
        $charset = $this->db->get_charset_collate();

        return "CREATE TABLE $tableName (
            id int(10) NOT NULL AUTO_INCREMENT,
            code INTEGER(3),
            symb VARCHAR(3),
            nominal VARCHAR(6),
            rate VARCHAR(12),
            created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset ;";
    }

    /**
     * Get last record date
     * @return mixed
     */
    public function getLastUpdatedDate()
    {
        $name = $this->getTableName();
        $lastRecord = $this->db->get_var("SELECT created FROM $name ORDER BY created DESC LIMIT 1");

        return ($lastRecord != null)
            ? $this->getFormattedDate(new DateTime($lastRecord))
            : $this->getYesterdayDate();
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
        $currency = !$currency
            ? $currency = $this->getDefaultChartCurrency()
            : (int) $currency;
        $name = $this->getTableName();
        $limit = $this->getDaysForChart();

        $sql = "SELECT rate, created FROM $name WHERE code = $currency ORDER BY created DESC LIMIT $limit ";
        $result = $this->db->get_results($sql, ARRAY_A);

        return json_encode($result);
    }

    public function getClassName()
    {
        return 'GgauzExchange';
    }
}
