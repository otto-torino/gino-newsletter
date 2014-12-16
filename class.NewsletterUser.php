<?php
/**
 * @file class.NewsletterUser.php
 * Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterUser
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

use \Gino\DatetimeField;
use \Gino\ManyToManyField;

/**
 * @brief Classe tipo Gino.Model che rappresenta un iscritto alla newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class NewsletterUser extends \Gino\Model {

    public static $table = 'newsletter_user';
    public static $table_ctgs = 'newsletter_user_ctg';

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Newsletter.NewsletterUser
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'categories'=>_("Categorie"),
            'date'=>_("Data di iscrizione"),
            'firstname'=>_("Nome"),
            'lastname'=>_("Cognome"),
            'cap'=>_("Cap"),
            'email'=>_("Email"),
            'notes'=>_("Note"),
        );

        parent::__construct($id);

        $this->_model_label = _('Iscritto');

    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return cognome nome
     */
    function __toString() {
        return $this->id ? $this->lastname.' '.$this->firstname : '';
    }

    /**
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['date'] = new DatetimeField(array(
            'name'=>'date',
            'model'=>$this,
            'auto_now'=>FALSE,
            'auto_now_add'=>TRUE,
        ));

        $structure['categories'] = new ManyToManyField(array(
            'name'=>'categories',
            'model'=>$this,
            'm2m'=>'\Gino\App\Newsletter\NewsletterCtg',
            'join_table'=>self::$table_ctgs,
            'add_related' => TRUE,
            'add_related_url' => $this->_registry->router->link('newsletter', 'manageNewsletter', array(), 'block=ctg&insert=1'),
        ));

        return $structure;

    }

    /**
     * @brief Numero di utenti
     * @param array $opts array associativo di opzioni (where)
     * @return numero utenti
     */
    public static function getCount($opts=null) {

        $where = \Gino\gOpt($opts, 'where', '');

        $res = 0;

        $db = Db::instance();
        return $db->getNumRecords(self::$table, $where);

    }

}
