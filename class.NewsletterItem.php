<?php
/**
 * @file class.NewsletterItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterItem
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

use \Gino\Loader;
use \Gino\BooleanField;
use \Gino\DatetimeField;

/**
 * @brief Classe tipo Gino.Model che rappresenta un articolo della newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class NewsletterItem extends \Gino\Model {

    public static $table = 'newsletter_item';

    /**
     * Costruttore
     *
     * @param object $instance istanza del controller
     * @return istanza di Gino.App.Newsletter.NewsletterItem
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'date_creation'=>_("Data di creazione"),
            'date'=>_("Data di iscrizione"),
            'date_last_edit'=>_("Data di ultima modifica"),
            'subject'=>_("Oggetto"),
            'text'=>_("Testo"),
            'date_last_send'=>_("Data di ultimo invio"),
            'public'=>_("Pubblica"),
        );

        parent::__construct($id);

        $this->_model_label = _('Articolo');

    }

    /**
     * Rappresentazione a stringa dell'oggetto
     * @return subject
     */
    public function __toString()
    {
        return (string) $this->subject;
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

        $structure['public'] = new BooleanField(array(
            'name'=>'public',
            'model'=>$this,
            'enum'=>array(1 => _('si'), 0 => _('no')),
        ));

        $structure['date_creation'] = new DatetimeField(array(
            'name'=>'date_creation',
            'model'=>$this,
            'auto_now'=>FALSE,
            'auto_now_add'=>TRUE
        ));

        $structure['date_last_edit'] = new DatetimeField(array(
            'name'=>'date_last_edit',
            'model'=>$this,
            'auto_now'=>TRUE,
            'auto_now_add'=>TRUE
        ));

        $structure['date_last_send'] = new DatetimeField(array(
            'name'=>'date_last_send',
            'model'=>$this,
            'auto_now'=>FALSE,
            'auto_now_add'=>FALSE
        ));

        $default = "<!DOCTYPE html>\n";
        $default .= "<html>\n";
        $default .= "<head>\n";
        $default .= "<title></title>\n";
        $default .= "<meta charset=\"utf-8\" />\n";
        $default .= "</head>\n";
        $default .= "<body>\n";
        $default .= "</body>\n";
        $default .= "</html>\n";

        Loader::import('newsletter', 'NewsletterItemTextField');
        $structure['text'] = new NewsletterItemTextField(array(
            'name'=>'text', 
            'model'=>$this,
        ));

        $structure['text']->setDefault($default);

        return $structure;
    }

    /**
     * Visualizzazione newsletter
     *
     * @return visualizzazione newsletter
     */
    public function view() {

        preg_match_all("#{{[^}]+}}#", $this->text, $matches);
        $buffer = $this->parseTemplate($this->text, $matches);

        return $this->htmlCharsNewsletter($buffer);

    }

    /**
     * Preview newsletter
     *
     * @return preview newsletter
     */
    public function preview($html) {

        preg_match_all("#{{[^}]+}}#", $html, $matches);
        $buffer = $this->parseTemplate($html, $matches);

        return $this->htmlCharsNewsletter($buffer);

    }

    /**
     * @brief Parserizzazione del testo newsletter per sostituzione contenuti esterni o link
     *
     * @return testo parserizzato
     */
    private function parseTemplate($tpl, $matches) {

        $controller = new newsletter();

        if(isset($matches[0])) {
            foreach($matches[0] as $m) {

                $code = trim(preg_replace("#{|}#", "", $m));

                if($code == 'unregister_url' || $code == 'view_url' || $code == 'archive_url') {
                    if($code == 'unregister_url') {
                        $tpl = preg_replace("#".preg_quote($m)."#", $controller->link('newsletter', 'unregisterEmail', array('c'=>$controller->getUnregisterCode()), array(), array('abs' => TRUE)), $tpl);
                    }
                    elseif($code == 'archive_url') {
                        $tpl = preg_replace("#".preg_quote($m)."#", $controller->link('newsletter', 'archive', array(), array(), array('abs'=>TRUE)), $tpl);
                    }
                    elseif($code == 'view_url') {
                        $tpl = preg_replace("#".preg_quote($m)."#", $controller->link('newsletter', 'view', array('id' => $this->id), array(), array('abs' => TRUE)), $tpl);
                    }
                }
                elseif(preg_match("#\w+\|(\d+)? \d+#", $code)) {

                    list($class_info, $id) = explode(' ', $code);

                    list($class_name, $class_id) = explode('|', $class_info);
                    $class = get_app_name_class_ns($class_name);

                    if(class_exists($class)) {
                        $object = new $class($class_id);

                        if(!method_exists($object, 'systemNewsletterRender')) {
                            $replace = '';
                        }
                        else {
                            $replace = $object->systemNewsletterRender($id);
                        }
                        $tpl = preg_replace("#".preg_quote($m)."#", $replace, $tpl);
                    }
                }
            }
        }

        return $tpl;
    }

    /**
     * @brief Pulisce il testo da db per essere mandato via email
     *
     * @param string $string testo da pulire
     * @return testo pulito
     */
    private function htmlCharsNewsletter($string) {

        $request = \Gino\Http\Request::instance();
        $string = trim($string);
        $string = stripslashes($string);

        $string = str_replace ('&euro;', '€', $string);
        $string = str_replace ('\'', '&#039;', $string);
        //$string = preg_replace("/:/", "&#58;", $string);
        $string = preg_replace("#(src=['\"])(?!http)#", "$1http://".$request->META['HTTP_HOST'].'/', $string);
        $string = preg_replace("#(href=['\"])(?!http)#", "$1http://".$request->META['HTTP_HOST'].'/', $string);
        $string = preg_replace("#".preg_quote($request->META['HTTP_HOST'].'//')."#", $request->META['HTTP_HOST'].'/', $string);

        return $string;
    }

    /**
     * @brief Update della data di ultimo invio
     * @return void
     */
    public function updateLastSend() {
        $this->date_last_send = date("Y-m-d H:i:s");
        $this->save();
    }

}
