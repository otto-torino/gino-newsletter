<?php
/**
* @file log.php
* @brief Template per la vista dei log di invio
*
* Variabili disponibili:
* - **title**: titolo della vista
* - **tot_send**: totale invii
* - **table**: tabella log invii
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
<? if($tot_send): ?>
<?= $table ?>
<? else: ?>
<p><?= _('La newsletter non è stata inviata') ?></p>
<? endif ?>
</section>
<? // @endcond ?>
