<?php
/**
* @file empty_options.php
* @brief Template restituito quando non sono state compilate le opzioni obbligatorie
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **link_options**: url delle opzioni del modulo
*
* @version 0.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Newsletter; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
<h1><?= $title ?></h1>
<p><?= _('<b>Attenzione</b>, l\'invio di newsletter richiede la compilazione dei campi obbligatori presenti nella sezione opzioni.') ?></p>
<p><a href="<?= $link_options ?>"><?= _('Vai alle opzioni') ?></a></p>
</section>
<? // @endcond ?>
