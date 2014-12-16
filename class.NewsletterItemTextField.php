<?php
/**
 * @file class.NewsletterItemTextField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Newsletter.NewsletterItemTextField
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Newsletter;

use \Gino\Db;

/**
 * @brief Classe tipo Gino.TextField per gestire il campo testo dell'articolo newsletter
 *
 * @version 0.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class NewsletterItemTextField extends \Gino\TextField {

    public function clean($options = null) {

        $request = \Gino\Http\Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $escape = \Gino\gOpt('escape', $options, TRUE);

        $text = $method[$this->_name];
        $text = trim($text);
        if(get_magic_quotes_gpc()) $text = stripslashes($text);    // magic_quotes_gpc = On

        $text = \Gino\replaceChar($text);
        $text = str_replace ('€', '&euro;', $text);    // con DB ISO-8859-1

        if($escape)
        {
            $db = Db::instance();
            $text = $db->escapeString($text);
        }

        return $text;
    }
}
