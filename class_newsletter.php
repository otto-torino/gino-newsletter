<?php
/**
 * @file class_newsletter.php
 * Contiene la definizione ed implementazione della classe Gino.App.Newsletter.newsletter
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Newsletter
 * @description Namespace dell'applicazione Newsletter
 */
namespace Gino\App\Newsletter;

use \Gino\Loader;
use \Gino\Registry;
use \Gino\View;
use \Gino\Form;
use \Gino\AdminTable;

require_once('class.NewsletterCtg.php');
require_once('class.NewsletterUser.php');
require_once('class.NewsletterItem.php');
require_once('class.NewsletterLog.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione di newsletter.
 *
 * Gli output disponibili sono:
 *
 * - box di registrazione
 * - archivio newsletter pubbliche
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class newsletter extends \Gino\Controller {

    /**
     * istanza della classe opzioni
     */
    private $_options;

    /**
     * Etichette dei campi di opzioni
     */
    public $_optionsLabels;

    /**
     * indirizzo email che compare nel campo from
     */
    private $_from_email;

    /**
     * nome che compare nel campo from
     */
    private $_from_name;

    /**
     * indirizzo email che compare nel campo to
     */
    private $_to_email;

    /**
     * nome che compare nel campo to
     */
    private $_to_name;

    /**
     * envelope sender email
     */
    private $_return_path;

    /**
     * codice per la disiscrizione
     */
    private $_unregister_code;

    /**
     * numero di email inviate per blocco
     */
    private $_emails_for_block;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Newsletter.newsletter
     */
    function __construct(){

        parent::__construct();

        $this->_from_email = $this->setOption('from_email');
        $this->_from_name = $this->setOption('from_name');
        $this->_to_email = $this->setOption('to_email');
        $this->_to_name = $this->setOption('to_name');
        $this->_return_path = $this->setOption('return_path');
        $this->_test_email = $this->setOption('test_email');

        // the second paramether will be the class instance
        $this->_options = new \Gino\Options($this);
        $this->_optionsLabels = array(
            "from_name"=>array("label"=>_("Nome che compare nel campo From"), "required"=>false, 'trnsl' => false),
            "from_email"=>array('label'=>_("Indirizzo email che compare nel campo From"), 'trnsl'=>false),
            "to_name"=>array("label"=>_("Nome che compare nel campo To"), "required"=>false, 'trnsl'=>false),
            "to_email"=>array('label' => _("Indirizzo email che compare nel campo To"), 'trnsl' => false),
            "return_path"=>array('label' => _("Envelope sender email"), 'trnsl' => false),
            "test_email"=>array("label"=>array(_("Indirizzo email di test"), _("Indirizzo al quale viene inviata una copia della newsletter")), "required"=>false, 'trnsl'=>false),
        );

        $this->_unregister_code = md5('unregister_'.$this->_registry->sysconf->head_title);
        $this->_emails_for_block = 100;

    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return lista delle proprietà (tabelle, css, viste, folders)
     */
    public static function getClassElements() 
    {
        return array(
            "tables"=>array(
                'newsletter_ctg',
                'newsletter_item',
                'newsletter_log',
                'newsletter_log_error',
                'newsletter_opt',
                'newsletter_user',
                'newsletter_user_ctg',
            ),
            "css"=>array(
                'newsletter.css',
            ),
            "views" => array(
                'archive.php' => _('Archivio newsletter'),
                'empty_options.php' => _('Area amministrativa, opzioni non inserite'),
                'item_admin_table_form.php' => _('Inserimento/modifica item newsletter'),
                'log.php' => _('Log di invio'),
                'manage_send.php' => _('Gestione invio newsletter'),
                'newsletter_module_list.php' => _('Lista elementi del modulo che esporta i contenuti'),
                'registration.php' => _('Form di registrazione'),
                'unregister.php' => _('Cancellazione registrazione'),
            ),
        );
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (metodi non presenti nel file news.ini) e dal motore di generazione
     * di voci di menu (metodi presenti nel file news.ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo NOME_METODO => array('label' => LABEL, 'permissions' => PERMESSI)
     */
    public static function outputFunctions() {

        $list = array(
            "registration" => array("label"=>_("Form di registrazione alla newsletter"), "permissions"=>array()),
            "registrationBox" => array("label"=>_("Box form di registrazione alla newsletter"), "permissions"=>array()),
            "archive" => array("label"=>_("Archivio newsletter"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * Getter della proprietà unregister_code
     * @return proprietà unregister_code
     */
    public function getUnregisterCode() {

        return $this->_unregister_code;
    }

    /**
     * @brief Vista form di registrazione
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function registration(\Gino\Http\Request $request)
    {
        $view_content = $this->registrationBox();
        $document = new \Gino\Document($view_content);
        return $document();
    }

    /**
     * @brief Box di registrazione alla newsletter
     * @return html, box di registrazione
     */
    public function registrationBox() {

        $registry = Registry::instance();
        $registry->addCss($this->_class_www.'/newsletter.css');
        $request = $registry->request;

        $msg = '';

        if(isset($request->POST['newsletter_registration_submit_action'])) {

            $gform = Loader::load('Form', array('gform', 'post', TRUE));
            $req_error = $gform->arequired();

            $check = NewsletterUser::objects(null, array("where"=>"email='".\Gino\cleanVar($request->POST, 'email', 'string')."'"));
            if($req_error > 0) {
                $msg = _("Inserire tutti i campi obbligatori");
            }
            elseif(count($check)) {
                $msg = _("L'email inserita è già presente nel sistema");
            }
            elseif(!$gform->checkCaptcha()) {
                $msg = _("Il codice di controllo inserito non è corretto");
            }
            else {

                $user = new NewsletterUser(null, $this);
                $user->date = date("Y-m-d H:i:s");
                $user->firstname = \Gino\cleanVar($request->POST, 'firstname', 'string', '');
                $user->lastname = \Gino\cleanVar($request->POST, 'lastname', 'string', '');
                $user->cap = \Gino\cleanVar($request->POST, 'cap', 'string', '');
                $user->email = \Gino\cleanVar($request->POST, 'email', 'string', '');

                $user->save();

                $msg = sprintf(_("Grazie per esserti registrato alla newsletter di %s"), $this->_registry->sysconf->head_title);
            }

        }

        $gform = Loader::load('Form', array('gform', 'post', TRUE));

        $required = 'firstname,lastname,email';
        $form = $gform->open('', true, $required);
        $form .= $gform->cinput('firstname', 'text', $gform->retvar('firstname', ''), _('nome'), array("size"=>25, "maxlength"=>64, "required" => true));
        $form .= $gform->cinput('lastname', 'text', $gform->retvar('lastname', ''), _('cognome'), array("size"=>25, "maxlength"=>64, "required" => true));
        $form .= $gform->cinput('cap', 'text', $gform->retvar('cap', ''), _('cap'), array("size"=>5, "maxlength"=>5, "required" => false));
        $form .= $gform->cinput('email', 'email', $gform->retvar('email', ''), _('email'), array("size"=>25, "maxlength"=>64, "required" => true));
        $form .= $gform->captcha();
        $form .= $gform->cinput('newsletter_registration_submit_action', 'submit', _("iscriviti"), '', array("classField"=>"submit"));
        $form .= $gform->close();

        $view = new view($this->_view_dir, 'registration');
        $view->assign('section_id', 'registration_newsletter');
        $view->assign('title', _('Newsletter'));
        $view->assign('form', $form);
        $view->assign('archive_url', $this->link($this->_class_name, 'archive'));
        $view->assign('msg', $msg);

        return $view->render();

    }

    /**
     * @bief Archivio newsletter pubbliche inviate
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function archive(\Gino\Http\Request $request) {

        $itemsobj = NewsletterItem::objects(null, array("where"=>"public='1'", "order"=>"date_creation DESC"));
        $items = array();
        foreach($itemsobj as $obj) {
            $items[] = array(
                'url' => $this->link($this->_class_name, 'view', array('id' => $obj->id)),
                'date' => \Gino\dbDatetimeToDate($obj->date_creation, '/'),
                'subject' => \Gino\htmlChars($obj->subject)
            );
        }

        $view = new view($this->_view_dir, 'archive');
        $view->assign('section_id', 'archive_newsletter');
        $view->assign('title', _('Archivio newsletter'));
        $view->assign('items', $items);

        $document = new \Gino\Document($view->render());
        return $document();

    }

    /**
     * @brief Disiscrizione email
     * @param \Gino\Http\Request $request
     * @throws Gino.Exception.Exception404 se il codice via GET è errato
     * @return Gino.Http.Response, form e azione di disiscrizione
     */
    public function unregisterEmail(\Gino\Http\Request $request) {

        $c = \Gino\cleanVar($request->GET, 'c', 'string');

        if($c != $this->_unregister_code) {
            throw new \Gino\Exception\Exception404();
        }

        if(isset($request->POST['submit_action'])) {
            $gform = Loader::load('Form', array('gform', 'post', TRUE, array("verifyToken"=>TRUE)));
            $req_error = $gform->arequired();
            if($req_error > 0) {
                return error::errorMessage(array('error'=>_("Inserire un indirizzo email")), $this->_home."?evt[$this->_class_name-unregisterEmail]&c=$c");
            }

            $email = \Gino\cleanVar($request->POST, 'email', 'string');

            $nusers = NewsletterUser::objects(null, array('where'=>"email='$email'"));
            if($nusers and count($nusers)) {
                $nuser = $nusers[0];
                $nuser->delete();
                $message = sprintf(_("La registrazione alla newsletter di %s è stata cancellata"), $this->_registry->sysconf->head_title);
            }
            else {
                $message = sprintf(_("Indirizzo email non presente"), $this->_registry->sysconf->head_title);
            }
            $form = null;
        }
        else {

            $gform = Loader::load('Form', array('gform', 'post', TRUE));
            $gform->load('dataform');

            $message = _("Inserisci il tuo indirizzo email per procedere alla disiscrizione dal servizio.");

            $required = 'email';
            $form = $gform->open('', '', $required, array("generateToken"=>TRUE));
            $form .= $gform->hidden('c', $c);
            $form .= $gform->cinput('email', 'text', '', _("email"), array("required"=>true, "size"=>40, "maxlength"=>200));
            $form .= $gform->cinput('submit_action', 'submit', _("disiscrivi"), '', array("classField"=>"submit"));

            $form .= $gform->close();

        }

        $title = _("Cancellazione registrazione newsletter");

        $view = new view($this->_view_dir, 'unregister');
        $view->assign('section_id', 'unregister_newsletter');
        $view->assign('title', $title);
        $view->assign('message', $message);
        $view->assign('form', $form);

        $document = new \Gino\Document($view->render());
        return $document();
    }

    /**
     * @brief Visualizzazione newsletter
     *
     * @param \Gino\Http\Request $request
     * @throws Gino.Exception.Exception404 se la newsletter non viene trovata
     * @return Gino.Http.Response
     */
    public function view(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->GET, 'id', 'int');

        $item = new NewsletterItem($id, $this);

        $buffer = $item->view();

        return new \Gino\Http\Response($buffer);

    }

    /**
     * @brief Preview newsletter
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function preview(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $html = $request->POST['html'];

        $html = urldecode($html);

        $item = new NewsletterItem(null, $this);

        $buffer = $item->preview($html);

        return new \Gino\Http\Response($buffer);

    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function manageNewsletter(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=ctg'), _('Categorie iscritti'));
        $link_archive = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=archive'), _('Archivio'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Iscritti'));
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'ctg') {
            $backend = $this->manageCtg();
            $sel_link = $link_ctg;
        }
        elseif($block == 'archive') {
            $backend = $this->manageArchive($request);
            $sel_link = $link_archive;
        }
        elseif($block == 'send') {
            $id = \Gino\cleanVar($request->GET, 'id', 'int');
            $item = new NewsletterItem($id, $this);
            $backend = $this->manageSend($item, $request);
            $sel_link = $link_archive;
        }
        else {
            $backend = $this->manageUser();
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $links_array = array($link_frontend, $link_options, $link_dft, $link_ctg, $link_archive);

        $view = new View(null, 'tab');
        $dict = array(
            'title' => _('Newsletter'),
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Controllo compilazione opzioni obbligatorie
     *
     * @return FALSE se le opzioni sono presenti, html con avvertimento se non lo sono
     */
    private function emptyOptions() {

        if(!($this->_from_email && $this->_to_email && $this->_return_path)) {
            $view = new view($this->_view_dir, 'empty_options');
            $view->assign('section_id', 'empty_options_newsletter');
            $view->assign('title', _('Comunicazione'));
            $view->assign('link_options', $this->linkAdmin(array(), 'block=options'));

            return $view->render();
        }
        else return FALSE;
    }

    /**
     * @brief Gestione invio newsletter
     *
     * @param \Gino\App\Newsletter\NewsletterItem $item istanza di una newsletter Gino.App.Newsletter.NewsletterItem
     * @param \Gino\Http\Request $request
     * @return html, backoffice della gestione degli invii
     */
    private function manageSend($item, $request) {


        if($error = $this->emptyOptions()) {
            return $error;
        }

        $title = sprintf(_("Newsletter \"%s\""), \Gino\htmlChars($item->subject));

        $gform = Loader::load('Form', array('testmail', 'post', TRUE));
        $gform->load('testmail');

        $required = 'to';
        $form_test = $gform->open($this->link($this->_class_name, 'sendTestEmail'), '', $required);
        $form_test .= $gform->hidden('id', $item->id);
        $form_test .= $gform->cinput('email', 'text', '', _("Email di test"), array("required"=>true, "size"=>20, "maxlength"=>200));
        $form_test .= $gform->cinput('submit_action', 'submit', _("invia"), _("Invia la newsletter alla email di test specificata"), array("classField"=>"submit"));

        $form_test .= $gform->close();

        $gform = new Form('sendmail', 'post', TRUE);
        $gform->load('sendmail');

        $required = 'to';
        $form_send = $gform->open($this->link($this->_class_name, 'sendNewsletter'), '', $required);
        $form_send .= $gform->hidden('id', $item->id);
        $form_send .= $gform->cselect('category', $gform->retvar('category', ''), NewsletterCtg::getForSelect(),  _("Categoria di iscritti"), array("required"=>FALSE, 'noFirst'=>TRUE, 'firstVoice'=>_("tutti"), 'firstValue'=>0));
        $form_send .= $gform->cinput('submit_action', 'submit', _("invia"), '', array("classField"=>"submit"));

        $form_send .= $gform->close();

        $log = $this->viewLog($item);

        $view = new view($this->_view_dir, 'manage_send');
        $view->assign('title', $title);
        $view->assign('form_test', $form_test);
        $view->assign('form_send', $form_send);
        $view->assign('log', $log);

        return $view->render();

    }

    /**
     * @brief Visualizzazione log di invio
     * 
     * @param \Gino\App\Newsletter\NewsletterItem istanza di Gino.App.Newsletter.NewsletterItem
     * @return html, vista log invio
     */
    private function viewLog($item) {

        $heads = array(
            _('Data'),
            _('Categoria'),
            _('Errori di invio')
        );
 
        $logs = NewsletterLog::getFromNewsletter($item->id);

        $tot_send = count($logs);
        $rows = array();

        if(count($logs)) {
            foreach($logs as $logid) {
                $log = new NewsletterLog($logid);
                $ctg = new NewsletterCtg($log->category(), $this);
                $datetime = \Gino\dbDatetimeToDate($log->logdate(), "/")." ".\Gino\dbDatetimeToTime($log->logdate());
                $errors = $log->errors();

                $message = '';
                if(!$log->success()) {
                    $message .= "<p>"._("Errore! Lo script di invio delle newsletter non ha terminato l'esecuzione.")."</p>";
                }
                if(count($errors)) {
                    $message .= "<span class=\"link\" onclick=\"window.myWin = new gino.layerWindow({'title':'".sprintf(_("Email non inviate il %s a causa di errore"), $datetime)."', 'html':'".implode(",", $errors)."', 'width':600, 'height':300, 'destroyOnClose':true, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true }); window.myWin.display();\">"._("Visualizza")."</span>";
                }
                else {
                    $message .= "<p>"._("Nessun errore verificatosi nei blocchi di email spediti")."</p>";
                }

                $rows[] = array(
                    "<p>".$datetime."</p>",
                    "<p>".\Gino\htmlChars($ctg->name)."</p>",
                    $message
                );
            }
        }

        $view = new view(null, 'table');

        $view->assign('class', 'table table-striped table-hover');
        $view->assign('caption', sprintf(_('Numero di invii: %s'), $tot_send));
        $view->assign('heads', $heads);
        $view->assign('rows', $rows);

        $table = $view->render();

        $view = new view($this->_view_dir, 'log');
        $view->assign('title', _('Log invii newsletter'));
        $view->assign('table', $table);
        $view->assign('tot_send', $tot_send);

        return $view->render();

    }

    /**
     * @brief Backoffice gestione archivio newsletter
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect o html
     */
    private function manageArchive(\Gino\Http\Request $request) {

        require_once('class.NewsletterItemAdminTable.php');

        $this->_registry->addJs($this->_class_www.'/newsletter.js');

        $insert = isset($request->GET['insert']) ? TRUE : FALSE;
        $edit = isset($request->GET['edit']) ? TRUE : FALSE;

        $admin_table = new NewsletterItemAdminTable($this, array());

        $onclick = "onclick=\"window.myWin = new gino.layerWindow({'title':'Preview', 'html':'<iframe id=\'newsletter_preview\' frameborder=\'0\' width=\'840\' height=\'400\'></iframe>', 'width':900, 'overlay': false, 'destroyOnClose':true, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true});window.myWin.display();cbkPreview();\"";
        $gform = new Form('', '', '');
        $preview_button = array('name' => 'preview', 'field' =>$gform->cinput('preview', 'button', _("preview"), array(_('Preview'), _('Si aggiorna automaticamente togliendo il focus al campo di testo')), array("id"=>"preview", "classField"=>"generic", "js"=>$onclick)));
        $addCell = array(
            'public'=>$preview_button
        );

        $onblur = "onblur=\"
            if(window.myWin.showing) {
                cbkPreview();
            }
        \"";

        $backend = $admin_table->backOffice(
                'NewsletterItem',
                array(
                    'list_display' => array('id', 'date_last_edit', 'subject', 'date_last_send', 'public'),
                    'list_title'=>_("Elenco articoli newsletter"),
                    'filter_fields'=>array('subject', 'public'),
                    'add_buttons'=>array(
                        array(
                            'label' => "<span class=\"fa fa-eye icon-tooltip\" title=\""._('anteprima')."\"></span>",
                            'link' => $this->link($this->_class_name, 'view'),
                        ),
                        array(
                            'label' => "<span class=\"fa fa-envelope icon-tooltip\" title=\""._('gestione invio')."\"></span>",
                            'link' => $this->link($this->_class_name, 'manageNewsletter', array(), 'block=send'),
                        )
                    )
                ),
                array(
                    'addCell' => $addCell,
                    'removeFields'=>array('date_last_send') 
                ),
                array(
                    'subject' => array(
                        'size' => 60
                    ),
                    'text' => array(
                        'id' => 'text',
                        'cols' => '80',
                        'rows' => 40,
                        'js' => $onblur
                    )
                )
        );

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        if($insert or $edit) {
            $backend .= "<div id=\"newsletter_preview_mid\" style=\"display: none\"></div>";
            $backend .= "<script>";
            $backend .= "function cbkPreview() {
                var myHTMLRequest = new Request({url: '".$this->link($this->_class_name, 'preview')."', data: 'html=' + encodeURIComponent($('text').value), onSuccess: function(responseText, responseXML) {
                        $('newsletter_preview').contentWindow.document.open();
                        $('newsletter_preview').contentWindow.document.close();
                        $('newsletter_preview').contentWindow.document.write(responseText);
                }}).send();
            }";
            $backend .= "</script>";
        }

        return $backend;
    }

    /**
     * @brief Lista dei codici di inserimento contenuti esterni
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function newsletterModuleList(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $type = \Gino\cleanVar($request->GET, 'type', 'string');
        $name = \Gino\cleanVar($request->GET, 'name', 'string');
        $id = \Gino\cleanVar($request->GET, 'id', 'int');
        $class = get_app_name_class_ns($name);
        $obj = new $class($id);

        $items = $obj->systemNewsletterList();

        $view = new view($this->_view_dir, 'newsletter_module_list');
        $view->assign('items', $items);
        $view->assign('name', $name);
        $view->assign('id', $id);

        $document = new \Gino\Document($view->render());
        return $document();

    }

    /**
     * @brief Invio di della newsletter agli iscritti selezionati 
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function sendNewsletter(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int');
        $n = new NewsletterItem($id);

        date_default_timezone_set("Europe/Rome");
        $datetime = date("Y-m-d H:i:s");

        $category = \Gino\cleanVar($request->POST, 'category', 'int');

        $log = new NewsletterLog();
        $log->logShipment($n->id, $datetime, $category);

        $emails = array();
        if($category) {
            $where_ar[] = "id IN (SELECT newsletteruser_id FROM ".NewsletterUser::$table_ctgs." WHERE newsletterctg_id='".$category."')";
            $users = NewsletterUser::objects($this, array('field'=>'email', 'where'=>$where));
        }
        else {
            $users = NewsletterUser::objects($this, array('field'=>'email'));
        }

        if($users and count($users)) {
            foreach($users as $user) {
                $emails[] = $user->email;
            }
        }

        $to = $this->_to_name ? $this->_to_name." <".$this->_to_email.">" : $this->_to_email;

        $blocks = array_chunk($emails, $this->_emails_for_block);

        foreach($blocks as $k=>$block) {
            if(!mail($to, \Gino\htmlChars($n->subject), $n->view(), $this->emailHeaders($n, implode(",", $block)), '-f'.$this->_return_path)) {
                $log->logError($log->id(), implode(",",$block));
            }
        }
        if($this->_test_email) {
            if(!mail($this->_test_email, \Gino\htmlChars($n->subject), $n->view(), $this->emailHeaders($n), '-f'.$this->_return_path)) {
                $log->logError($log->id(), $this->_test_email);
            }
        }

        $log->logSuccess();

        $n->updateLastSend();

        return new \Gino\Http\Redirect($this->link($this->_class_name, 'manageNewsletter', array(), 'block=send&id='.$n->id));
    }

    /**
     * @brief Invio di della newsletter alla email di test specificata 
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function sendTestEmail(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $item = new NewsletterItem($id, $this);

        $email = \Gino\cleanVar($request->POST, 'email', 'string', '');
        $to = $this->_to_name ? $this->_to_name." <".$this->_to_email.">" : $this->_to_email;

        $result = mail($to, \Gino\htmlChars($item->subject), $item->view(), $this->emailHeaders($item, $email), '-f'.$this->_return_path);

        return new \Gino\Http\Redirect($this->link($this->_class_name, 'manageNewsletter', array(), 'block=send&id='.$item->id));

    }

    /**
     * Headers della mail inviata
     *
     * @param \Gino\App\Newsletter\NewsletterItem istanza di Gino.App.Newsletter.NewsletterItem
     * @param string $bcc email alla quale inviare
     * @return string, headers
     */
    private function emailHeaders($n, $bcc=null) {

        $from = $this->_from_name ? $this->_from_name." <".$this->_from_email.">" : $this->_from_email;

        $type = 'html';

        $headers = '';

        if($from) $headers .= "From: ".$from."\n";
        if($bcc) $headers .= 'Bcc: '.$bcc."\n";

        $headers  .= 'MIME-Version: 1.0'."\n";

        if($type == 'plain') $headers .= $this->textEmailPart();
        elseif($type == 'html') $headers .= $this->htmlEmailPart();

        return $headers;

    }

    /**
     * Content type di email plain
     * @return content type
     */
    private function textEmailPart(){

        $message = 'Content-type: text/plain; charset=utf-8'."\n";

        return $message;
    }

    /**
     * Content type di email html
     *
     * @return content type
     */
    private function htmlEmailPart(){

        $message = 'Content-type: text/html; charset=utf-8'."\n";

        return $message;
    }

    /**
     * @brief Backoffice gestione categorie di iscritti 
     *
     * @return Gino.Http.Redirect oppure html
     */
    private function manageCtg() {

        $admin_table = new AdminTable($this, array());

        $backend = $admin_table->backOffice(
                'NewsletterCtg',
                array(
                    'list_display' => array('id', 'name'),
                    'list_title'=>_("Elenco categorie di iscritti"),
                    'list_description'=>"<p>"._('Ciascuna iscritto potrà essere associato ad una o più categorie qui definite.')."</p>",
                ),
                array(),
                array()
        );

        return $backend;
    }

    /**
     * @brief Backoffice gestione iscritti
     *
     * @return Gino.Http.Redirect oppure html
     */
    private function manageUser() {

        $admin_table = new AdminTable($this, array());

        $backend = $admin_table->backOffice(
                'NewsletterUser',
                array(
                    'list_display' => array('id', 'categories', 'date', 'firstname', 'lastname', 'email'),
                    'list_title'=>_("Elenco iscritti"),
                    'filter_fields'=>array('categories', 'firstname', 'lastname', 'email')
                ),
                array(),
                array(
                    'firstname' => array('trnsl' => FALSE),
                    'lastname' => array('trnsl' => FALSE),
                    'cap' => array('trnsl' => FALSE),
                    'email' => array('trnsl' => FALSE),
                    'notes' => array('trnsl' => FALSE),
                )
        );

        return $backend;

    }

}
