<?php
/**
 * @file class.NewsletterCtg.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterCtg.
 * 
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

/**
 * @brief Classe tipo Gino.Model che rappresenta una categoria di iscritti alla newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class NewsletterCtg extends \Gino\Model {

    public static $table = 'newsletter_ctg';

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Newsletter.NewsletterCtg
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name'=>_("Nome")
        );

        parent::__construct($id);

        $this->_model_label = _('Categoria');

    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {
        return (string) $this->name;
    }

    /**
     * @brief Restituisce un array associativo di categorie id=>nome
     *
     * @return array associativo id=>nome
     */
    public static function getForSelect() {

        $res = array();

        $db = \Gino\Db::instance();
        $rows = $db->select('id, name', self::$table, null, null);
        if($rows && sizeof($rows)>0) {
            foreach($rows as $row) {
                $res[$row['id']] = \Gino\htmlChars($row['name']);
            }
        }

        return $res;

    }

}
