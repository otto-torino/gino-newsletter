<?php
/**
 * @file class.NewsletterLog.php
 * Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterLog
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

use \Gino\Db;

/**
 * @brief Classe che rappresenta il log di un invio newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class NewsletterLog {

    /**
     * istanza del db
     */
    private $_db;

    /**
     * identificativo 
     */
    private $_id;

    /**
     * identificativo newsletter 
     */
    private $_newsletter;

    /**
     * data del log
     */
    private $_logdate;

    /**
     * email invio fallito
     */
    private $_errors;

    /**
     * tabella log
     */
    private $_tbl_log;

    /**
     * tabella errori
     */
    private $_tbl_log_error;

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Newsletter.NewsletterLog
     */
    public function __construct($id = null) {

        $this->_db = Db::instance();
        $this->_tbl_log = "newsletter_log";
        $this->_tbl_log_error = "newsletter_log_error";

        $this->initObject($id);

    }

    /**
     * @brief Inizializza un istanza di NewsletterLog
     *
     * @param int $id identificativo del'oggetto
     * @return istanza di Gino.App.Newsletter.NewsletterLog
     */
    private function initObject($id) {

        if(!$id) return null;

        $this->_id = $id;
        $this->_errors = array();
        $rows = $this->_db->select(array('newsletter', 'logdate', 'category', 'success'), $this->_tbl_log, "id='".$id."'");
        if($rows and count($rows)) {
            $this->_newsletter = $rows[0]['newsletter'];
            $this->_logdate = $rows[0]['logdate'];
            $this->_category = $rows[0]['category'];
            $this->_success = $rows[0]['success'];
        }

        $rows = $this->_db->select(array('emails'), $this->_tbl_log_error, "shipment='".$id."'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $this->_errors[] = $row['emails'];
            }
        }

        return $this;

    }

    /**
     * Resitituisce gli identificativi dei log connessi alla newsletter
     *
     * @param int $nid identificativo della newsletter
     * @return array di id di log
     */
    public static function getFromNewsletter($nid) {

        $db = \Gino\Db::instance();
        $res = array();

        $rows = $db->select('id', 'newsletter_log', "newsletter='".$nid."'", array('order' => 'logdate DESC'));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = $row['id'];
            }
        }

        return $res;

    }

    /**
     * @brief Getter della proprietà id

     * @return proprietà id
     */
    public function id() {

        return $this->_id;
    }

    /**
     * @brief Getter della proprietà newsletter

     * @return proprietà newsletter
     */
    public function newsletter() {

        return $this->_newsletter;
    }

    /**
     * @brief Getter della proprietà logdate

     * @return proprietà logdate
     */
    public function logdate() {

        return $this->_logdate;
    }

    /**
     * @brief Getter della proprietà category

     * @return proprietà category
     */
    public function category() {

        return $this->_category;
    }

    /**
     * @brief Getter della proprietà success
     *
     * @return proprietà success
     */
    public function success() {

        return $this->_success;
    }

    /**
     * @brief Getter della proprietà errors

     * @return proprietà errors
     */
    public function errors() {

        return $this->_errors;
    }

    /**
     * @brief Logga un errore 
     *
     * @param int $sid identificativo dell'invio
     * @param string $emails email per le quali si è verificato un errore di invio
     * @return void
     */
    public function logError($sid, $emails) {

        return $this->_db->insert(array('shipment' => $sid, 'emails' => $emails), $this->_tbl_log_error);

    }

    /**
     * @brief Logga un invio
     *
     * @param int $nid identificativo della newsletter
     * @param string $datetime data e ora di invio
     * @param int $category categoria di iscritti
     * @return void
     */
    public function logShipment($nid, $datetime, $category) {

        $result = $this->_db->insert(array('newsletter' => $nid, 'logdate' => $datetime, 'category' => $category), $this->_tbl_log);

        $this->_id = $this->_db->getlastid($this->_tbl_log);

    }

    /**
     * @brief Logga un successo
     *
     * @return void
     */
    public function logSuccess() {

        return $this->_db->update(array('success' => 1), $this->_tbl_log, "id='".$this->_id."'");

    }

}
