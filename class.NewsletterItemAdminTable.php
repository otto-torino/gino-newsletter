<?php
/**
 * @file class.NewsletterItemAdminTable.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterItemAdminTable
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

use \Gino\View;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

/**
 * @brief Classe per la gestione del backoffice di un elemento della newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class NewsletterItemAdminTable extends \Gino\AdminTable {

    /**
     * Costruttore
     *
     * @param \Gino\App\Newsletter\newsletter $instance
     * @param array $opts
     *   array associativo di opzioni
     *   - @b view_folder (string): percorso della directory contenente la vista da caricare
     *   - @b allow_insertion (boolean): indica se permettere o meno l'inserimento di nuovi record
     *   - @b edit_deny (mixed): indica quali sono gli ID dei record che non posssono essere modificati
     *     - @a string, 'all' -> tutti
     *     - @a array, elenco ID
     *   - @b delete_deny (mixed): indica quali sono gli ID dei record che non posssono essere eliminati
     *     - @a string, 'all' -> tutti
     *     - @a array, elenco ID
     */
    function __construct($instance, $opts = array()) {

        parent::__construct($instance, $opts);
    }

    /**
     * Wrapper per mostrare e processare il form
     * @see Gino.AdminTable::adminForm()
     */
    public function adminForm($model_obj, $options_form, $inputs) {

        $db = \Gino\Db::instance();

        $sys_modules = array();

        $modules_app = ModuleApp::objects(null, array('where' => "instantiable='0' AND active='1'", 'order' => 'label'));
        if($modules_app && count($modules_app)) {
            foreach($modules_app as $module_app) {
               if(method_exists($module_app->classNameNs(), 'systemNewsletterList')) {
                    $sys_modules[] = array(
                        'label' => \Gino\htmlChars($module_app->label),
                        'description' => \Gino\htmlChars($module_app->description),
                        'onclick' => "if(!window.myWin".$module_app->id.$module_app->name." || !window.myWin".$module_app->id.$module_app->name.".showing) {window.myWin".$module_app->id.$module_app->name." = new gino.layerWindow({'title':'".\Gino\htmlChars($module_app->label).' - '._("elementi inseribili")."', 'url':'".$this->_registry->router->link('newsletter', 'newsletterModuleList', array(), "type=sysmodule&name=".$module_app->name)."', 'bodyId':'newsletter_".$module_app->id.$module_app->name."', 'width':600, 'maxHeight':400, 'destroyOnClose':true, overlay: false, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true, reloadZindex:true});window.myWin".$module_app->id.$module_app->name.".display();}"
                    );
                } 
            }
        }

        $modules = array();

        $mdls = ModuleInstance::objects(null, array('where' => "active='1'", 'order' => 'label'));
        if($mdls && count($mdls)) {
            foreach($mdls as $mdl) {
               if(method_exists($mdl->classNameNs(), 'systemNewsletterList')) {
                    $sys_modules[] = array(
                        'label' => \Gino\htmlChars($mdl->label),
                        'description' => \Gino\htmlChars($mdl->description),
                        'onclick' => "if(!window.myWin".$mdl->id.$mdl->name." || !window.myWin".$mdl->id.$mdl->name.".showing) {window.myWin".$mdl->id.$mdl->name." = new gino.layerWindow({'title':'".\Gino\htmlChars($mdl->label).' - '._("Lista elementi inseribili")."', 'url':'".$this->_registry->router->link('newsletter', 'newsletterModuleList', array(), "type=module&name=".$mdl->className()."&id=".$mdl->id)."', 'bodyId':'newsletter_".$mdl->id.$mdl->name."', 'width':600, 'maxHeight':400, 'destroyOnClose':true, overlay: false, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true, reloadZindex:true});window.myWin".$mdl->id.$mdl->name.".display();}"
                    );
                } 
            }
        }

        $unregister_url = "{{ unregister_url }}";
        $view_url = "{{ view_url }}";
        $archive_url = "{{ archive_url }}";

        if($this->_request->method === 'POST') {
            $insert = !$model_obj->id;
            $popup = \Gino\cleanVar($this->_request->POST, '_popup', 'int');
            // link error
            $link_error = $this->editUrl(array(), array());
            $options_form['link_error'] = $link_error ;
            // action
            $action_result = $this->modelAction($model_obj, $options_form, $inputs);
            // link success
            if(isset($options_form['link_return']) and $options_form['link_return']) {
                $link_return = $options_form['link_return'];
            }
            else {
                if(isset($this->_request->POST['save_and_continue']) and !$insert) {
                    $link_return = $this->editUrl(array(), array());
                }
                elseif(isset($this->_request->POST['save_and_continue']) and $insert) {
                    $link_return = $this->editUrl(array('edit' => 1, 'id' => $model_obj->id), array('insert'));
                }
                else {
                    $link_return = $this->editUrl(array(), array('insert', 'edit', 'id'));
                }
            }

            if($action_result === TRUE and $popup) {
                $script = "<script>opener.gino.dismissAddAnotherPopup(window, '$model_obj->id', '".htmlspecialchars((string) $model_obj, ENT_QUOTES)."' );</script>";
                return new \Gino\Http\Response($script, array('wrap_in_document' => FALSE));
            }
            elseif($action_result === TRUE) {
                return new \Gino\Http\Redirect($link_return);
            }
            else {
                return Error::errorMessage($action_result, $link_error);
            }
        }
        else {

            // edit
            if($model_obj->id) {
                if($this->_edit_deny == 'all' || in_array($model_obj->id, $this->_edit_deny)) {
                    throw new \Gino\Exception403();
                }
                $title = sprintf(_("Modifica \"%s\""), \Gino\htmlChars((string) $model_obj));
            }
            // insert
            else {
                if(!$this->_allow_insertion) {
                    throw new \Gino\Exception403();
                }
                $title = sprintf(_("Inserimento %s"), $model_obj->getModelLabel());
            }

            $form = $this->modelForm($model_obj, $options_form, $inputs);

            $view = new View(dirname(__FILE__).OS.'views');
            $view->setViewTpl('item_admin_table_form');
            $view->assign('title', $title);
            $view->assign('form', $form);
            $view->assign('sys_modules', $sys_modules);
            $view->assign('modules', $modules);
            $view->assign('unregister_url', $unregister_url);
            $view->assign('view_url', $view_url);
            $view->assign('archive_url', $archive_url);

            return $view->render();

        }
    }

}

?>
