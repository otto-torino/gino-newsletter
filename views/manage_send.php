<?php
/**
* @file manage_send.php
* @brief Template per la vista gestione invio newsletter
*
* Variabili disponibili:
* - **title**: titolo della vista
* - **form_test**: form per l'invio ad una email di test
* - **form_send**: form per l'invio ad una o tutte le categorie di iscritti
* - **log**: log invii newsletter
*
* @version 0.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Newsletter; ?>
<? //@cond no-doxygen ?>
<section>
<h1><?= $title ?></h1>
<div class="left" style="width:49%">
    <h2><?= _('Invio email di test') ?></h2>
    <?= $form_test ?>
</div>
<div class="right" style="width:49%">
    <h2><?= _('Invio email a iscritti') ?></h2>
    <?= $form_send ?>
</div>
<div class="null"></div>
<?= $log ?>
</section>
<? // @endcond ?>
