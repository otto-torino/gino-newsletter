<?php
/**
* @file newsletter_module_list.php
* @ingroup newsletter
* @brief Template per la vista lista elementi del modulo che esporta contenuti
*
* Variabili disponibili:
* - **items**: array di array associativi rappresentanti i contenuti esportabili (chiavi dipendenti dal modulo in questione). Sempre presente la chiave id: identificativo del contenuto
* - **name**: nome del modulo
* - **id**: id del modulo
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
<? if(count($items)): ?>
<table class="table table-striped table-hover">
<tr>
    <? foreach($items[0] as $k=>$v): ?>
        <th><?= $k ?></th>
    <? endforeach ?>
        <th><?= _('Codice inserimento') ?></th>
</tr>
    <? foreach($items as $item): ?>
    <tr>
        <? foreach($item as $k=>$v): ?>
            <td><?= $v ?></td>
        <? endforeach ?>
        <td>{{ <?= $name ?>|<?= $id ?> <?= $item['id'] ?> }}</td>
    </tr>
    <? endforeach ?>
</table>
<? else: ?>
<p><?= _('Nessun elemento presente') ?></p>
<? endif ?>
</section>
<? // @endcond ?>
