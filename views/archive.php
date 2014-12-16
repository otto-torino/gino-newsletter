<?php
/**
* @file archive.php
* @brief Template per la vista archivio newsletter
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **items**: array di array associativi rappresentanti articoli newsletter. proprietà:
*     - date: data di creazione  
*     - date: url dettaglio  
*     - subject: subject dell'email  
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
<? if(count($items)): ?>
<dl>
    <? foreach($items as $item): ?>
        <dt><?= $item['date'] ?></dt>
        <dd><a rel="external" href="<?= $item['url'] ?>"><?= $item['subject'] ?></a></dd>
    <? endforeach ?>
</dl>
<? else: ?>
<p><? _('Non risultano newsletter pubblicate') ?></p>
<? endif ?>
</section>
<? // @endcond ?>
