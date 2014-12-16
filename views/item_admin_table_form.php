<?php
/**
 * @file item_admin_table_form.php
 * @brief Template per il form di inserimento/modifica di un item della newsletter
 *
 * Variabili disponibili:
 * - **title**: titolo della vista
 * - **sys_modules**: moduli di sistema che esportano contenuti per la newsletter. Array di array associativi. Proprietà:
 *     - onclick: azione da effettuare al click (visione codice di inserimento contenuti)
 *     - label: label modulo
 *     - description: descrizione modulo
 * - **modules**: moduli istanziati che esportano contenuti per la newsletter. Array di array associativi. Proprietà:
 *     - onclick: azione da effettuare al click (visione codice di inserimento contenuti)
 *     - label: label modulo
 *     - description: descrizione modulo
 * - **form**: form inserimento/modifica newsletter
 * - **unregister_url**: codice per ottenere l'url che porta alla disiscrizione
 * - **view_url**: codice per ottenere l'url che porta alla vista sul sito
 * - **archive_url**: codice per ottenere l'url che porta alla vista archivio sul sito
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
<? if(count($sys_modules) || count($modules)): ?>
<h2><?= _('Importazione contenuti esterni') ?></h2>
<table class="table table-bordered">
<tr>
<td style="width:50%">
<p><?= _('Alcuni moduli presenti nel sistema rendono disponibili i propri contenuti per l\'importazione negli articoli delle newsletter.') ?></p>
<p><?= _('Cliccare sul modulo di interesse, scorrere la lista e prelevare il codice dato per l\'inserimento del contenuto nel punto desiderato dell\'articolo.') ?></p>
<p><?= _('Il layout esportato da ciascun modulo può essere impostato direttamente nelle opzioni del modulo stesso, o modificando la view corrispondente.') ?></p>
</td>
<td>
<dl>
<? foreach($sys_modules as $sm): ?>
    <dt><span class="link" onclick="<?= $sm['onclick'] ?>"><?= $sm['label'] ?></span></dt>
    <dd><?= $sm['description'] ?></dd>
<? endforeach ?>

<? foreach($modules as $m): ?>
    <dt><span class="link" onclick="<?= $m['onclick'] ?>"><?= $m['label'] ?></span></dt>
    <dd><?= $m['description'] ?></dd>
<? endforeach ?>
</dl>
</td>
</tr>
</table>
<? endif ?>
<h2><?= _('Url a disposizione') ?></h2>
<p>Inserire i seguenti codici al posto degli url desiderati:</p>
<ul>
<li><b><?= $unregister_url ?></b>: url che porta alla pagina di disiscrizione dalla newsletter</li> 
<li><b><?= $view_url ?></b>: url che porta alla visualizzazione della newsletter sul sito</li> 
<li><b><?= $archive_url ?></b>: url che porta alla visualizzazione dell'archivio newsletter sul sito</li> 
</ul>
<?= $form ?>
</section>
<? // @endcond ?>
